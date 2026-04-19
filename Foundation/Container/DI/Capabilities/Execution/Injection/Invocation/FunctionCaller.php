<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Execution\Injection\Invocation;

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Container\DI\Capabilities\Resolution\ResolvePlan;
use Avax\Container\DI\Capabilities\Resolution\ResolveRequest;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Throwable;

/**
 * Calls functions, closures, and methods with container-resolved arguments.
 */
final class FunctionCaller
{
    private ServiceResolver|null $resolver = null;

    /** @var array<string, ResolvePlan> */
    private array                         $plans = [];
    private readonly ResolveCallArguments $arguments;

    public function __construct(
        ResolveCallArguments $arguments
    )
    {
        $this->arguments = $arguments;
    }

    /**
     * Attaches the runtime resolver used for argument resolution.
     */
    public function setResolver(ServiceResolver $resolver) : void
    {
        $this->resolver = $resolver;
    }

    /**
     * Calls one target with container-resolved arguments.
     *
     * @param callable|string      $target
     * @param array<string, mixed> $parameters
     * @param ResolveRequest|null  $request
     *
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function call(
        callable|string     $target,
        array|null          $parameters = null,
        ResolveRequest|null $request = null
    ) : mixed
    {
        $parameters ??= [];
        if ($this->resolver === null) {
            throw new ContainerException(message: 'FunctionCaller is not attached to a resolver.');
        }

        $normalized = $this->normalizeTarget(target: $target, request: $request);
        $reflection = $this->reflect(target: $normalized);
        $arguments  = $this->arguments->resolvePlan(
            plan     : $this->planFor(reflection: $reflection),
            overrides: $parameters,
            resolver : $this->resolver,
            request  : $request ?? new ResolveRequest(serviceId: $this->nameOf(reflection: $reflection))
        );

        if ($reflection instanceof ReflectionMethod) {
            $object = is_object($normalized)
                ? $normalized
                : (is_array($normalized) && is_object($normalized[0]) ? $normalized[0] : null);

            return $reflection->invokeArgs(object: $object, args: $arguments);
        }

        /** @var ReflectionFunction $reflection */
        return $reflection->invokeArgs(args: $arguments);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     * @throws Throwable
     * @throws Throwable
     * @throws Throwable
     * @throws Throwable
     * @throws Throwable
     * @throws Throwable
     * @throws Throwable
     */
    private function normalizeTarget(callable|string $target, ResolveRequest|null $request = null) : callable|string|array
    {
        $context = $request?->context ?? [];

        if (is_string($target) && class_exists($target) && method_exists($target, '__invoke')) {
            return $context !== []
                ? $this->resolver->makeInContext(id: $target, parameters: [], context: $context)
                : $this->resolver->get(id: $target);
        }

        if (is_string($target) && str_contains($target, '@')) {
            [$class, $method] = explode('@', $target, 2);

            return [
                $context !== []
                    ? $this->resolver->makeInContext(id: $class, parameters: [], context: $context)
                    : $this->resolver->get(id: $class),
                $method
            ];
        }

        if (is_string($target) && str_contains($target, '::')) {
            [$class, $method] = explode('::', $target, 2);
            $reflection = new ReflectionMethod(objectOrMethod: $class, method: $method);

            return $reflection->isStatic()
                ? [$class, $method]
                : [
                    $context !== []
                        ? $this->resolver->makeInContext(id: $class, parameters: [], context: $context)
                        : $this->resolver->get(id: $class),
                    $method
                ];
        }

        if (is_array($target) && is_string($target[0]) && class_exists($target[0])) {
            $reflection = new ReflectionMethod(objectOrMethod: $target[0], method: (string) $target[1]);
            if (! $reflection->isStatic()) {
                return [
                    $context !== []
                        ? $this->resolver->makeInContext(id: $target[0], parameters: [], context: $context)
                        : $this->resolver->get(id: $target[0]),
                    $target[1]
                ];
            }
        }

        return $target;
    }

    /**
     * @throws ReflectionException
     */
    private function reflect(callable|string|array $target) : ReflectionFunctionAbstract
    {
        if (is_array($target)) {
            return new ReflectionMethod(objectOrMethod: $target[0], method: (string) $target[1]);
        }

        if ($target instanceof Closure || is_string($target)) {
            return new ReflectionFunction(function: $target);
        }

        if (is_object($target) && method_exists($target, '__invoke')) {
            return new ReflectionMethod(objectOrMethod: $target, method: '__invoke');
        }

        throw new ContainerException(message: 'Unsupported callable target.');
    }

    private function planFor(ReflectionFunctionAbstract $reflection) : ResolvePlan
    {
        $key = $this->planKeyOf(reflection: $reflection);

        if (isset($this->plans[$key])) {
            return $this->plans[$key];
        }

        return $this->plans[$key] = $this->arguments->createPlan(parameters: $reflection->getParameters());
    }

    private function planKeyOf(ReflectionFunctionAbstract $reflection) : string
    {
        if ($reflection instanceof ReflectionMethod) {
            return 'method:' . $reflection->class . '::' . $reflection->getName();
        }

        if ($reflection instanceof ReflectionFunction && $reflection->isClosure()) {
            return 'closure:' . ($reflection->getFileName() ?: 'internal') . ':' . $reflection->getStartLine() . ':' . $reflection->getEndLine();
        }

        return 'function:' . $reflection->getName();
    }

    private function nameOf(ReflectionFunctionAbstract $reflection) : string
    {
        if ($reflection instanceof ReflectionMethod) {
            return 'call:' . $reflection->class . '::' . $reflection->getName();
        }

        return 'call:' . $reflection->getName();
    }

    /**
     * Clears cached argument plans for previous callable reflections.
     */
    public function clearCache() : void
    {
        $this->plans = [];
    }
}
