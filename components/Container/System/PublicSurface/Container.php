<?php

declare(strict_types=1);

namespace Avax\Components\Container\System\PublicSurface;

final class Container implements ContainerInterface
{
    private array $bindings = [];

    public function make(string $id): mixed
    {
        return $this->bindings[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    public function call(callable $callback, array $args = []): mixed
    {
        return $callback(...array_values($args));
    }

    public function bind(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory();
    }
}