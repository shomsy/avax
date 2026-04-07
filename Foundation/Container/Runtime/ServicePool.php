<?php

declare(strict_types=1);

namespace Avax\Container\Runtime;

final class ServicePool
{
    /** @var array<string, mixed> */
    private array $items = [];

    public function has(string $abstract) : bool
    {
        return array_key_exists($abstract, $this->items);
    }

    public function get(string $abstract) : mixed
    {
        return $this->items[$abstract] ?? null;
    }

    public function set(string $abstract, mixed $instance) : void
    {
        $this->items[$abstract] = $instance;
    }

    public function forget(string $abstract) : void
    {
        unset($this->items[$abstract]);
    }

    public function flush() : void
    {
        $this->items = [];
    }
}
