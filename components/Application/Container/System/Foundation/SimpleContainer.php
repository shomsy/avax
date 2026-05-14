<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Foundation;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * Minimal in-memory container for examples and testing.
 *
 * This is NOT the production container. It exists only to support
 * examples, tests, and development scenarios where a full container
 * is not available.
 */
final class SimpleContainer implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, Closure|class-string> */
    private array $bindings = [];

    /** @var array<string, bool> */
    private array $singletons = [];

    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new class('Entry ['.$id.'] is not in the container.') extends RuntimeException implements NotFoundExceptionInterface {};
        }

        return $this->resolve($id);
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->bindings[$id]);
    }

    public function call(callable $callback, array $parameters = []): mixed
    {
        return $callback(...$parameters);
    }

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
    {
        $concrete ??= $abstract;
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = $shared;
    }

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, shared: true);
    }

    public function scoped(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, shared: false);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function alias(string $alias, string $abstract): void
    {
        if (isset($this->instances[$abstract])) {
            $this->instances[$alias] = $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $this->bindings[$alias] = $this->bindings[$abstract];
            $this->singletons[$alias] = $this->singletons[$abstract];
        }
    }

    public function tag(string|array $abstracts, string|array $tags): void
    {
        // Not supported in SimpleContainer
    }

    public function tagged(string $tag): array
    {
        return [];
    }

    public function flush(): void
    {
        $this->instances = [];
        $this->bindings = [];
        $this->singletons = [];
    }

    private function resolve(string $id, array $parameters = []): mixed
    {
        if (isset($this->instances[$id]) && $this->singletons[$id]) {
            return $this->instances[$id];
        }

        $concrete = $this->bindings[$id] ?? $id;

        if ($concrete instanceof Closure) {
            $resolved = $concrete($this);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            $resolved = new $concrete(...$parameters);
        } else {
            throw new class('Unable to resolve ['.$id.'] from container.') extends RuntimeException implements ContainerExceptionInterface {};
        }

        if ($this->singletons[$id] ?? false) {
            $this->instances[$id] = $resolved;
        }

        return $resolved;
    }
}
