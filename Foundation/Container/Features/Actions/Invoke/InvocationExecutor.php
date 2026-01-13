<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Invoke;

use Avax\Container\Core\Kernel\Contracts\KernelContext;
use Avax\Container\Features\Actions\Invoke\Cache\ReflectionCache;
use Avax\Container\Features\Actions\Invoke\Context\InvocationContext;
use Avax\Container\Features\Actions\Resolve\Contracts\DependencyResolverInterface;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Think\Model\ParameterPrototype;
use Closure;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * High-performance orchestrator for calling PHP callables with full dependency injection.
 *
 * The InvocationExecutor is the core component for "Call-time injection".
 * It takes any valid PHP callable (Closures, Method arrays, Static strings,
 * or "Class@method" syntax), resolves the target object if necessary,
 * resolves all arguments via the {@see DependencyResolverInterface}, and
 * executes the call. It includes an internal {@see ReflectionCache} to
 * ensure high-speed repeated executions.
 *
 * @see     docs/Features/Actions/Invoke/InvocationExecutor.md
 */
final readonly class InvocationExecutor
{
    /**
     * Initializes the executor with its resolution and caching collaborators.
     *
     * @param ContainerInterface          $container The container used to resolve parameter types and "Class@method"
     *                                               targets.
     * @param DependencyResolverInterface $resolver  The parameter resolver for finding argument values.
     * @param ReflectionCache             $cache     An internal cache for expensive reflection objects.
     */
    public function __construct(
        private ContainerInterface          $container,
        private DependencyResolverInterface $resolver,
        private ReflectionCache             $cache = new ReflectionCache
    ) {}

    /**
     * Execute a callable with automatic dependency resolution.
     *
     * @param InvocationContext    $context       The descriptor of the target callable.
     * @param array<string, mixed> $parameters    Manual parameter overrides (Name => Value).
     * @param KernelContext|null   $parentContext The current resolution context for loop detection.
     *
     * @return mixed The return value of the executed callable.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException If the callable or method cannot be reflected.
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-execute
     */
    public function execute(
        InvocationContext  $context,
        array|null         $parameters = null,
        KernelContext|null $parentContext = null
    ) : mixed
    {
        // 1. Resolve "Class@method" strings into real [Object, Method] pairs
        $parameters ??= [];
        $context    = $this->normalizeTarget(
            context      : $context,
            parentContext: $parentContext
        );

        // 2. Fetch/Cache reflection for the target
        $reflection = $this->getReflection(target: $context->getEffectiveTarget());
        $context    = $context->withReflection(reflection: $reflection);

        // 3. Create a kernel context for the call
        $kernelContext = new KernelContext(
            serviceId: $this->buildContextName(reflection: $reflection),
            overrides: $parameters,
            parent   : $parentContext
        );

        // 4. Resolve all parameters using the standard resolver
        $resolved = $this->resolver->resolveParameters(
            parameters: $this->buildParameterPrototypes(parameters: $reflection->getParameters()),
            overrides : $parameters,
            container : $this->container,
            context   : $kernelContext
        );

        $context = $context->withResolvedArguments(resolvedArguments: $resolved);

        // 5. Final Invocation
        return $this->invoke(context: $context);
    }

    /**
     * Normalize a target, resolving any deferred class strings into object instances.
     *
     * @param InvocationContext  $context       Initial context.
     * @param KernelContext|null $parentContext Parent resolution tracking.
     *
     * @return InvocationContext Normalized context.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-normalizetarget
     */
    private function normalizeTarget(
        InvocationContext  $context,
        KernelContext|null $parentContext
    ) : InvocationContext
    {
        $target = $context->getEffectiveTarget();

        if (! is_string($target) || ! str_contains($target, '@')) {
            return $context;
        }

        [$class, $method] = explode('@', $target, 2);
        if ($class === '' || $method === '') {
            throw new InvalidArgumentException(message: "Invalid Class@method target: {$target}");
        }

        // Resolving the class instance through the container
        $instance = $this->container->get(id: $class);

        return $context->withNormalizedTarget(normalizedTarget: [$instance, $method]);
    }

    /**
     * Retrieve a cached reflection object for the given target.
     *
     * @param mixed $target Target callable.
     *
     * @return ReflectionFunctionAbstract The reflection object.
     *
     * @throws ReflectionException If reflection fails.
     *
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-getreflection
     */
    private function getReflection(mixed $target) : ReflectionFunctionAbstract
    {
        $key    = $this->buildCacheKey(target: $target);
        $cached = $this->cache->get(key: $key);
        if ($cached !== null) {
            return $cached;
        }

        $reflection = $this->createReflection(target: $target);
        $this->cache->set(key: $key, reflection: $reflection);

        return $reflection;
    }

    /**
     * Generates a unique cache key for a given callable.
     *
     * @param mixed $target The callable target.
     *
     * @return string Unique identifier.
     *
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-buildcachekey
     */
    private function buildCacheKey(mixed $target) : string
    {
        if (is_array($target)) {
            $classOrObject = is_object($target[0]) ? get_class($target[0]) : (string) $target[0];

            return $classOrObject . '::' . $target[1];
        }

        if (is_string($target)) {
            return $target;
        }

        if ($target instanceof Closure) {
            return 'closure:' . spl_object_id($target);
        }

        if (is_object($target)) {
            return get_class($target) . '::__invoke';
        }

        return 'callable:' . gettype($target);
    }

    /**
     * Factory logic for creating the correct reflection type for any callable.
     *
     * @param mixed $target The target.
     *
     * @return ReflectionFunctionAbstract Function or Method reflection.
     *
     * @throws ReflectionException
     * @throws InvalidArgumentException
     *
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-createreflection
     */
    private function createReflection(mixed $target) : ReflectionFunctionAbstract
    {
        if (is_array($target)) {
            return new ReflectionMethod(objectOrMethod: $target[0], method: $target[1]);
        }

        if (is_string($target) && str_contains($target, '::')) {
            [$class, $method] = explode('::', $target, 2);

            return new ReflectionMethod(objectOrMethod: $class, method: $method);
        }

        if ($target instanceof Closure || is_string($target)) {
            return new ReflectionFunction(function: $target);
        }

        if (is_object($target) && method_exists($target, '__invoke')) {
            return new ReflectionMethod(objectOrMethod: $target, method: '__invoke');
        }

        throw new InvalidArgumentException(
            message: 'Unsupported callable type: ' . gettype($target) . '. Supported: arrays, strings, closures, and invokable objects.'
        );
    }

    /**
     * Builds a human-readable identifier for the current invocation context.
     *
     * @param ReflectionFunctionAbstract $reflection The reflection of the target.
     *
     * @return string Context name (e.g. "call:MyClass::execute").
     */
    private function buildContextName(ReflectionFunctionAbstract $reflection) : string
    {
        if ($reflection instanceof ReflectionMethod) {
            return 'call:' . $reflection->class . '::' . $reflection->getName();
        }

        return 'call:' . $reflection->getName();
    }

    /**
     * Transform native reflection parameters into container-aware prototypes.
     *
     * @param array $parameters List of reflection parameters.
     *
     * @return ParameterPrototype[] The mapped prototypes.
     *
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-buildparameterprototypes
     */
    private function buildParameterPrototypes(array $parameters) : array
    {
        $prototypes = [];

        foreach ($parameters as $parameter) {
            $prototypes[] = new ParameterPrototype(
                name      : $parameter->getName(),
                type      : $this->resolveType(type: $parameter->getType()),
                hasDefault: $parameter->isDefaultValueAvailable(),
                default   : $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                isVariadic: $parameter->isVariadic(),
                allowsNull: $parameter->allowsNull(),
                required  : ! $parameter->isDefaultValueAvailable() && ! $parameter->allowsNull()
            );
        }

        return $prototypes;
    }

    /**
     * Resolves the type name from a native reflection type.
     *
     * @param ReflectionType|null $type The reflection type.
     *
     * @return string|null The resolved class/interface name or null for primitives.
     *
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-resolvetype
     */
    private function resolveType(ReflectionType|null $type) : string|null
    {
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof ReflectionNamedType && ! $subType->isBuiltin()) {
                    return $subType->getName();
                }
            }
        }

        return null;
    }

    /**
     * Final execution step that applies arguments and triggers the callable.
     *
     * @param InvocationContext $context The fully prepared context.
     *
     * @return mixed The result of the call.
     *
     * @throws ReflectionException
     *
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-invoke
     */
    private function invoke(InvocationContext $context) : mixed
    {
        if (! $context->reflection instanceof ReflectionFunctionAbstract || ! is_array($context->resolvedArguments)) {
            return null;
        }

        if ($context->reflection instanceof ReflectionMethod) {
            return $context->reflection->invokeArgs(
                object: $this->resolveInvocationObject(context: $context),
                args  : $context->resolvedArguments
            );
        }

        /** @var ReflectionFunction $reflection */
        $reflection = $context->reflection;

        return $reflection->invokeArgs(args: $context->resolvedArguments);
    }

    /**
     * Determines the correct 'this' pointer for a method call.
     *
     * @param InvocationContext $context The current context.
     *
     * @return mixed The object instance or null for static/function calls.
     *
     * @see docs/Features/Actions/Invoke/InvocationExecutor.md#method-resolveinvocationobject
     */
    private function resolveInvocationObject(InvocationContext $context) : mixed
    {
        $target = $context->getEffectiveTarget();

        if (is_array($target)) {
            return is_object($target[0]) ? $target[0] : null;
        }

        return is_object($target) ? $target : null;
    }
}
