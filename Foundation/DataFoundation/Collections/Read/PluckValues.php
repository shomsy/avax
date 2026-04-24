<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Read;

/**
 * Plucks values by key or callable from collection items.
 */
final readonly class PluckValues
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param string|callable $key
     *
     * @return array<mixed>
     */
    public function __invoke(string|callable $key) : array
    {
        return $this->pluck(key: $key);
    }

    /**
     * @param string|callable $key
     *
     * @return array<mixed>
     */
    public function pluck(string|callable $key) : array
    {
        return array_map(
            callback: fn (mixed $item) : mixed => is_callable(value: $key)
                ? $key($item)
                : ($item[$key] ?? null),
            array   : $this->items
        );
    }

    public function getItems() : array
    {
        return $this->items;
    }
}