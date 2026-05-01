<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation;

use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolvePlan;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
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
    private ?ResolveDependency $resolveDependency = null;

    /** @var array<string, ResolvePlan> */
    private array $plans = [];

    public function __construct(private readonly ResolveCallArguments $resolveCallArguments)
    {
    }

    /**
     * Attaches the runtime resolver used for argument resolution.
     */
    public function setResolver(ResolveDependency $resolveDependency): void
    {
        $this->resolveDependency = $resolveDependency;
    }

    /**
     * Calls one target with container-resolved arguments.
     *
     * @param array<string, mixed> $parameters
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function call(
        callable|string $target,
        ?array $parameters = null,
        ?ResolveRequest $resolveRequest = null,
    ): mixed {
        $parameters ??= [];
        if (! $this->resolveDependency instanceof ResolveDependency) {
            throw new ContainerException(message: 'FunctionCaller is not attached to a resolver.');
        }

        $normalized                 = $this->normalizeTarget(target: $target, request: $resolveRequest);
        $reflectionFunctionAbstract = $this->reflect(target: $normalized);
        $arguments                  = $this->resolveCallArguments->resolvePlan(
            plan     : $this->planFor(reflection: $reflectionFunctionAbstract),
            overrides: $parameters,
            resolver : $this->resolveDependency,
            request  : $resolveRequest ?? new ResolveRequest(serviceId: $this->nameOf(reflection: $reflectionFunctionAbstract)),
        );

        if ($reflectionFunctionAbstract instanceof ReflectionMethod) {
            $object = is_object(value: $normalized)
                ? $normalized
                : (is_array(value: $normalized) && is_object(value: $normalized[0]) ? $normalized[0] : null);

            return $reflectionFunctionAbstract->invokeArgs(object: $object, args: $arguments);
        }

        return $reflectionFunctionAbstract->invokeArgs(args: $arguments);
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
    private function normalizeTarget(callable|string $target, ?ResolveRequest $resolveRequest = null): callable|string|array
    {
        $context = $resolveRequest?->context ?? [];

        if (is_string(value: $target) && class_exists(class: $target) && method_exists(object_or_class: $target, method: '__invoke')) {
            return $context !== []
                ? $this->resolveDependency->makeInContext(id: $target, parameters: [], context: $context)
                : $this->resolveDependency->get(id: $target);
        }

        if (is_string(value: $target) && str_contains(haystack: $target, needle: '@')) {
            [$class, $method] = explode(separator: '@', string: $target, limit: 2);

            return [
                $context !== []
                    ? $this->resolveDependency->makeInContext(id: $class, parameters: [], context: $context)
                    : $this->resolveDependency->get(id: $class),
                $method,
            ];
        }

        if (is_string(value: $target) && str_contains(haystack: $target, needle: '::')) {
            [$class, $method] = explode(separator: '::', string: $target, limit: 2);
            $reflection       = new ReflectionMethod(objectOrMethod: $class, method: $method);

            return $reflection->isStatic()
                ? [$class, $method]
                : [
                    $context !== []
                        ? $this->resolveDependency->makeInContext(id: $class, parameters: [], context: $context)
                        : $this->resolveDependency->get(id: $class),
                    $method,
                ];
        }

        if (is_array(value: $target) && is_string(value: $target[0]) && class_exists(class: $target[0])) {
            $reflection = new ReflectionMethod(objectOrMethod: $target[0], method: (string) $target[1]);
            if (! $reflection->isStatic()) {
                return [
                    $context !== []
                        ? $this->resolveDependency->makeInContext(id: $target[0], parameters: [], context: $context)
                        : $this->resolveDependency->get(id: $target[0]),
                    $target[1],
                ];
            }
        }

        return $target;
    }

    /**
     * @throws ReflectionException
     */
    private function reflect(callable|string|array $target): ReflectionFunctionAbstract
    {
        if (is_array(value: $target)) {
            return new ReflectionMethod(objectOrMethod: $target[0], method: (string) $target[1]);
        }

        if ($target instanceof Closure || is_string(value: $target)) {
            return new ReflectionFunction(function: $target);
        }

        if (is_object(value: $target) && method_exists(object_or_class: $target, method: '__invoke')) {
            return new ReflectionMethod(objectOrMethod: $target, method: '__invoke');
        }

        throw new ContainerException(message: 'Unsupported callable target.');
    }

    private function planFor(ReflectionFunctionAbstract $reflectionFunctionAbstract): ResolvePlan
    {
        $key = $this->planKeyOf(reflection: $reflectionFunctionAbstract);

        return $this->plans[$key] ?? ($this->plans[$key] = $this->resolveCallArguments->createPlan(parameters: $reflectionFunctionAbstract->getParameters()));
    }

    private function planKeyOf(ReflectionFunctionAbstract $reflectionFunctionAbstract): string
    {
        if ($reflectionFunctionAbstract instanceof ReflectionMethod) {
            return 'method:' . $reflectionFunctionAbstract->class . '::' . $reflectionFunctionAbstract->getName();
        }

        if ($reflectionFunctionAbstract instanceof ReflectionFunction && $reflectionFunctionAbstract->isClosure()) {
            return 'closure:' . ($reflectionFunctionAbstract->getFileName() ?: 'internal') . ':' . $reflectionFunctionAbstract->getStartLine() . ':' . $reflectionFunctionAbstract->getEndLine();
        }

        return 'function:' . $reflectionFunctionAbstract->getName();
    }

    private function nameOf(ReflectionFunctionAbstract $reflectionFunctionAbstract): string
    {
        if ($reflectionFunctionAbstract instanceof ReflectionMethod) {
            return 'call:' . $reflectionFunctionAbstract->class . '::' . $reflectionFunctionAbstract->getName();
        }

        return 'call:' . $reflectionFunctionAbstract->getName();
    }

    /**
     * Clears cached argument plans for previous callable reflections.
     */
    public function clearCache(): void
    {
        $this->plans = [];
    }
}
