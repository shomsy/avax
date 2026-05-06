<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Bindings;

use LogicException;

/**
 * Registry for service bindings, aliases, and tags.
 */
final class BindingRegistry
{
    /** @var array<string, array{concrete: mixed, shared: bool, scoped: bool}> */
    private array $bindings = [];

    /** @var array<string, string> */
    private array $aliases = [];

    /** @var array<string, string[]> */
    private array $tags = [];

    /** @var array<string, object> */
    private array $instances = [];

    public function singleton(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, shared: true);
    }

    public function bind(string $abstract, mixed $concrete = null, bool $shared = false, bool $scoped = false): void
    {
        $abstract = $this->resolveAlias($abstract);
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared,
            'scoped' => $scoped,
        ];
    }

    public function resolveAlias(string $abstract): string
    {
        return isset($this->aliases[$abstract])
            ? $this->resolveAlias($this->aliases[$abstract])
            : $abstract;
    }

    public function scoped(string $abstract, mixed $concrete = null): void
    {
        $this->bind($abstract, $concrete, shared: true, scoped: true);
    }

    public function instance(string $abstract, object $instance): void
    {
        $abstract = $this->resolveAlias($abstract);
        $this->instances[$abstract] = $instance;
    }

    public function alias(string $alias, string $abstract): void
    {
        if ($alias === $abstract) {
            throw new LogicException('Cannot alias a service to itself: '.$alias);
        }

        $this->aliases[$alias] = $abstract;
    }

    public function tag(string|array $abstracts, string|array $tags): void
    {
        foreach ((array) $abstracts as $abstract) {
            foreach ((array) $tags as $tag) {
                $this->tags[$tag][] = $this->resolveAlias($abstract);
            }
        }
    }

    public function tagged(string $tag): array
    {
        return $this->tags[$tag] ?? [];
    }

    public function has(string $abstract): bool
    {
        $abstract = $this->resolveAlias($abstract);

        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    public function getBinding(string $abstract): ?array
    {
        $abstract = $this->resolveAlias($abstract);

        return $this->bindings[$abstract] ?? null;
    }

    public function getInstance(string $abstract): ?object
    {
        $abstract = $this->resolveAlias($abstract);

        return $this->instances[$abstract] ?? null;
    }

    public function clear(): void
    {
        $this->bindings = [];
        $this->aliases = [];
        $this->tags = [];
        $this->instances = [];
    }

    public function clearScoped(): void
    {
        foreach ($this->bindings as $abstract => $binding) {
            if ($binding['scoped']) {
                unset($this->instances[$abstract]);
            }
        }
    }
}
