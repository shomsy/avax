<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Transform;

/**
 * Maps a callback over collection items.
 */
final readonly class MapValues
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param callable $callback fn(mixed $item, int|string $key): mixed
     *
     * @return array<mixed>
     */
    public function __invoke(callable $callback) : array
    {
        return $this->map(callback: $callback);
    }

    /**
     * @param callable $callback fn(mixed $item, int|string $key): mixed
     *
     * @return array<mixed>
     */
    public function map(callable $callback) : array
    {
        return array_map(callback: $callback, array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}