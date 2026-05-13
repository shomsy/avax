<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\ResolveCallable;

use Psr\Container\ContainerInterface;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

/**
 * Central callable and class-string resolution for AvaX runtime lanes.
 *
 * This is the single path all runtime lanes use to turn a class-string or callable
 * into an invokable handler. It replaces direct `new $listener()` / `new $handler()`
 * scattered across Events, Database lifecycle, Router, Queue, and FailureBoundary.
 *
 * Resolution order:
 * 1. If already a callable (Closure, invokable object), return as-is.
 * 2. If class-string and container is available, try container::make().
 * 3. If class-string and container not available or cannot resolve, fall back to zero-arg `new`.
 * 4. Reject non-invokable class-string.
 *
 * Method autowiring:
 * - resolveMethod([ClassName::class, 'methodName']) resolves instance + autowires method params
 * - resolveMethod('ClassName@methodName') parses string and resolves with autowiring
 * - Container resolves each method parameter by type
 * - Default values used when parameter is not resolvable
 *
 * Design constraints:
 * - Does not rewrite DI — optional PSR-11 container parameter only.
 * - No hot-path reflection — class_exists and is_callable only.
 * - Reflection allowed ONLY in resolveMethod (boot/dispatch path, not per-request hot path).
 * - No hidden global state — pure resolution per call.
 * - Future-ready for Boot DSL container injection.
 * - No EventInterface/ListenerInterface requirement.
 */
final class ResolveCallable
{
    public function __construct(
        private readonly ?ContainerInterface $container = null,
    ) {
    }

    /**
     * Resolve a class-string or callable into an invokable callable.
     *
     * @return callable
     *
     * @throws CallableResolutionFailed
     */
    public function resolve(callable|string $target): callable
    {
        // Already a callable — return as-is.
        if (is_callable($target)) {
            return $target;
        }

        // Class-string resolution.
        // @phpstan-ignore argument.type
        return $this->resolveClassString($target);
    }

    /**
     * Resolve and immediately invoke a target with the given arguments.
     *
     * @phpstan-param callable|class-string $target
     * @param  list<mixed>  $args
     * @return mixed
     *
     * @throws CallableResolutionFailed
     */
    public function invoke(callable|string $target, array $args = []): mixed
    {
        // @phpstan-ignore argument.type
        $callable = $this->resolve($target);

        return $callable(...$args);
    }

    /**
     * Resolve a method callable with method-level autowiring.
     *
     * Supports:
     * - [ClassName::class, 'methodName'] — array callable
     * - 'ClassName@methodName' — string callable
     * - [$instance, 'methodName'] — instance method
     *
     * Method parameters are resolved from the container by type.
     * Extra $args override container-resolved values.
     *
     * @param array{class-string|object, string}|string $target
     * @param list<mixed>                               $args
     * @return mixed
     *
     * @throws CallableResolutionFailed
     */
    public function invokeMethod(array|string $target, array $args = []) : mixed
    {
        [$instance, $method] = $this->parseMethodCallable($target);

        $resolvedArgs = $this->autowireMethodArgs($instance, $method, $args);

        return $instance->{$method}(...$resolvedArgs);
    }

    /**
     * Parse a method callable target into [instance, methodName].
     *
     * @param array{class-string|object, string}|string $target
     * @return array{object, string}
     *
     * @throws CallableResolutionFailed
     */
    private function parseMethodCallable(array|string $target) : array
    {
        if (is_array($target) && count($target) === 2) {
            [$classOrInstance, $method] = $target;
            $instance = is_object($classOrInstance) ? $classOrInstance : $this->resolveInstance($classOrInstance);

            return [$instance, $method];
        }

        if (is_string($target) && str_contains($target, '@')) {
            [$class, $method] = explode('@', $target, 2);
            $instance = $this->resolveInstance($class);

            return [$instance, $method];
        }

        throw new CallableResolutionFailed(sprintf(
                                               'Invalid method callable format. Expected [Class, method] or "Class@method", got: %s',
                                               is_array($target) ? json_encode($target) : $target,
                                           ));
    }

    /**
     * Autowire method arguments from container + provided args.
     *
     * @param list<mixed> $providedArgs
     *
     * @return list<mixed>
     */
    private function autowireMethodArgs(object $instance, string $method, array $providedArgs) : array
    {
        $reflection = new ReflectionMethod($instance, $method);
        $parameters = $reflection->getParameters();
        $resolved   = [];

        foreach ($parameters as $index => $param) {
            // Use provided arg if available
            if (array_key_exists($index, $providedArgs)) {
                $resolved[] = $providedArgs[$index];
                continue;
            }

            // Try container resolution by type
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $typeName = $type->getName();
                if ($this->container !== null && $this->container->has($typeName)) {
                    $resolved[] = $this->container->get($typeName);
                    continue;
                }
            }

            // Use default value if available
            if ($param->isDefaultValueAvailable()) {
                $resolved[] = $param->getDefaultValue();
                continue;
            }

            // Allow nullable parameters
            if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
                $resolved[] = null;
                continue;
            }

            throw new CallableResolutionFailed(sprintf(
                                                   'Cannot resolve parameter "$%s" in %s::%s(). No container binding, default value, or nullable type available.',
                                                   $param->getName(),
                                                   $instance::class,
                                                   $method,
                                               ));
        }

        return $resolved;
    }

    /**
     * Resolve a class instance through container or direct instantiation.
     *
     * @throws CallableResolutionFailed
     */
    private function resolveInstance(string $class) : object
    {
        if (! class_exists($class)) {
            throw new CallableResolutionFailed(sprintf(
                'Cannot resolve class-string "%s": class does not exist.',
                $class,
            ));
        }

        $instance = $this->tryContainer($class);

        if ($instance === null) {
            try {
                $instance = new $class();
            } catch (Throwable $e) {
                throw new CallableResolutionFailed(sprintf(
                    'Cannot instantiate "%s" with zero arguments. It may require constructor dependencies. Container integration would resolve this.',
                    $class,
                ), previous: $e);
            }
        }

        return $instance;
    }

    /**
     * Resolve a class-string to an invokable callable.
     *
     * @param class-string $class
     *
     * @return callable(mixed...): mixed
     *
     * @throws CallableResolutionFailed
     */
    private function resolveClassString(string $class) : callable
    {
        $instance = $this->resolveInstance($class);

        if (! is_callable($instance)) {
            throw new CallableResolutionFailed(sprintf(
                'Class "%s" is not invokable. It must implement __invoke() to be used as a runtime handler.',
                $class,
            ));
        }

        return $instance;
    }

    /**
     * Attempt to resolve a class through the optional PSR-11 container.
     *
     * Returns null if container is not available or cannot resolve the class.
     */
    private function tryContainer(string $class): ?object
    {
        if ($this->container === null) {
            return null;
        }

        try {
            return $this->container->get($class);
        } catch (Throwable) {
            // Container cannot resolve — fall back to zero-arg instantiation.
            return null;
        }
    }
}
