<?php

declare(strict_types=1);

namespace Avax\Container\Runtime;

/**
 * Stores shared runtime instances outside the scoped stack.
 */
final class ServicePool
{
    /** @var array<string, mixed> */
    private array $items = [];

    /**
     * Reports whether one shared instance exists.
     */
    public function has(string $abstract) : bool
    {
        return array_key_exists($abstract, $this->items);
    }

    /**
     * Returns one shared instance when available.
     */
    public function get(string $abstract) : mixed
    {
        return $this->items[$abstract] ?? null;
    }

    /**
     * Stores one shared instance.
     */
    public function set(string $abstract, mixed $instance) : void
    {
        $this->items[$abstract] = $instance;
    }

    /**
     * Removes one shared instance.
     */
    public function forget(string $abstract) : void
    {
        unset($this->items[$abstract]);
    }

    /**
     * Clears all shared instances.
     */
    public function flush() : void
    {
        $this->items = [];
    }

    /**
     * Returns the number of shared runtime instances.
     */
    public function count() : int
    {
        return count($this->items);
    }

    /**
     * @return list<string>
     */
    public function ids() : array
    {
        $ids = array_keys($this->items);
        sort($ids);

        return $ids;
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot() : array
    {
        return $this->items;
    }
}
