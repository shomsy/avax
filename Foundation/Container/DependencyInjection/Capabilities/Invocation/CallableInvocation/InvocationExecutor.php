<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Invocation\CallableInvocation;

use Avax\Container\DependencyInjection\Capabilities\Prototypes\Model\ParameterPrototype;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Engine\DependencyResolver;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\KernelContext;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Executes one prepared callable invocation using shared argument resolution.
 */
final readonly class InvocationExecutor
{
    public function __construct(
        private ContainerInterface $container,
        private DependencyResolver $resolver,
        private ReflectionCache $cache = new ReflectionCache
    ) {}

    public function execute(
        InvocationContext $context,
        array|null $parameters = null,
        KernelContext|null $parentContext = null
    ) : mixed
    {
        $parameters ??= [];
        $context    = $this->normalizeTarget(
            context      : $context,
            parentContext: $parentContext
        );

        $reflection = $this->getReflection(target: $context->getEffectiveTarget());
        $context    = $context->withReflection(reflection: $reflection);

        $kernelContext = new KernelContext(
            serviceId: $this->buildContextName(reflection: $reflection),
            overrides: $parameters,
            parent   : $parentContext
        );

        $resolved = $this->resolver->resolveParameters(
            parameters: $this->buildParameterPrototypes(parameters: $reflection->getParameters()),
            overrides : $parameters,
            container : $this->container,
            context   : $kernelContext
        );

        $context = $context->withResolvedArguments(resolvedArguments: $resolved);

        return $this->invoke(context: $context);
    }

    private function normalizeTarget(
        InvocationContext $context,
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

        $instance = $this->container->get(id: $class);

        return $context->withNormalizedTarget(normalizedTarget: [$instance, $method]);
    }

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

    private function buildContextName(ReflectionFunctionAbstract $reflection) : string
    {
        if ($reflection instanceof ReflectionMethod) {
            return 'call:' . $reflection->class . '::' . $reflection->getName();
        }

        return 'call:' . $reflection->getName();
    }

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

    private function resolveInvocationObject(InvocationContext $context) : mixed
    {
        $target = $context->getEffectiveTarget();

        if (is_array($target)) {
            return is_object($target[0]) ? $target[0] : null;
        }

        return is_object($target) ? $target : null;
    }
}
