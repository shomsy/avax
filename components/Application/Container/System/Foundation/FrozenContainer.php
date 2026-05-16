<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Foundation;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistration;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Closure;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

/**
 * FrozenContainer — SimpleContainer with real freeze/lock support.
 *
 * After freeze() is called:
 * - Mutation methods (bind, singleton, scoped, instance, alias, flush) throw LogicException.
 * - Read/resolve methods (get, has, make, call) still work.
 *
 * This is the minimal container for V5.9 first slice boot lifecycle.
 */
final class FrozenContainer implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, Closure|class-string> */
    private array $bindings = [];

    /** @var array<string, bool> */
    private array $singletons = [];

    private bool $frozen = false;

    /**
     * Freeze the container — no further mutations allowed.
     *
     * @throws LogicException if already frozen.
     */
    public function freeze(): void
    {
        if ($this->frozen) {
            throw new LogicException('Container is already frozen.');
        }

        $this->frozen = true;
    }

    /**
     * Returns true if the container has been frozen.
     */
    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    private function assertNotFrozen(string $method): void
    {
        if ($this->frozen) {
            throw new LogicException("Cannot call [{$method}] on a frozen container.");
        }
    }

    /** @param array<mixed> $parameters */
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

    /** @param array<mixed> $parameters */
    public function call(callable $callback, array $parameters = []): mixed
    {
        return $callback(...$parameters);
    }

    public function bind(string $abstract, mixed $concrete = null) : DependencyRegistration
    {
        $this->assertNotFrozen('bind');
        $concrete ??= $abstract;
        $this->bindings[$abstract] = $concrete;
        $this->singletons[$abstract] = false;

        return new DependencyRegistration(abstract: $abstract);
    }

    public function singleton(string $abstract, mixed $concrete = null) : DependencyRegistration
    {
        $this->assertNotFrozen('singleton');
        $this->bind($abstract, $concrete);
        $this->singletons[$abstract] = true;

        return new DependencyRegistration(abstract: $abstract);
    }

    public function scoped(string $abstract, mixed $concrete = null) : DependencyRegistration
    {
        $this->assertNotFrozen('scoped');
        $this->bind($abstract, $concrete);
        $this->singletons[$abstract] = false;

        return new DependencyRegistration(abstract: $abstract);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->assertNotFrozen('instance');
        $this->instances[$abstract] = $instance;
    }

    public function alias(string $alias, string $abstract): void
    {
        $this->assertNotFrozen('alias');
        if (isset($this->instances[$abstract])) {
            $this->instances[$alias] = $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $this->bindings[$alias] = $this->bindings[$abstract];
            $this->singletons[$alias] = $this->singletons[$abstract];
        }
    }

    /**
     * @param string|array<string> $abstracts
     * @param string|array<string> $tags
     */
    public function tag(string|array $abstracts, string|array $tags): void
    {
        $this->assertNotFrozen('tag');
        // Not supported in FrozenContainer
    }

    /** @return array<mixed> */
    public function debugGraph(string $id = '') : array
    {
        return [];
    }

    /** @return array<mixed> */
    public function tagged(string $tag): array
    {
        return [];
    }

    public function flush(): void
    {
        $this->assertNotFrozen('flush');
        $this->instances = [];
        $this->bindings = [];
        $this->singletons = [];
    }

    /** @param array<mixed> $parameters */
    private function resolve(string $id, array $parameters = []): mixed
    {
        // Return directly-set instances first
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $concrete = $this->bindings[$id] ?? $id;

        if ($concrete instanceof Closure) {
            $resolved = $concrete($this);
        } elseif (class_exists($concrete)) {
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
