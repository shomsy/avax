<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\ResolveCallable;

use Psr\Container\ContainerInterface;
use RuntimeException;

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
 * Design constraints:
 * - Does not rewrite DI — optional PSR-11 container parameter only.
 * - No hot-path reflection — class_exists and is_callable only.
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
     * Resolve a class-string to an invokable callable.
     *
     * @param  class-string  $class
     * @return callable(mixed...): mixed
     *
     * @throws CallableResolutionFailed
     */
    private function resolveClassString(string $class): callable
    {
        if (! class_exists($class)) {
            throw new CallableResolutionFailed(sprintf(
                'Cannot resolve class-string "%s": class does not exist.',
                $class,
            ));
        }

        // Try container first if available.
        $instance = $this->tryContainer($class);

        // Fall back to zero-arg instantiation.
        if ($instance === null) {
            try {
                $instance = new $class();
            } catch (\Throwable $e) {
                throw new CallableResolutionFailed(sprintf(
                    'Cannot instantiate "%s" with zero arguments. It may require constructor dependencies. Container integration would resolve this.',
                    $class,
                ), previous: $e);
            }
        }

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
        } catch (\Throwable) {
            // Container cannot resolve — fall back to zero-arg instantiation.
            return null;
        }
    }
}
