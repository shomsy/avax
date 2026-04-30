<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform;

/**
 * Filters collection items by callback.
 */
final readonly class FilterValues
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param callable $callback fn(mixed $item, int|string $key) : bool
     *
     * @return array<mixed>
     */
    public function __invoke(callable $callback) : array
    {
        return $this->filter(callback: $callback);
    }

    /**
     * @param callable $callback fn(mixed $item, int|string $key) : bool
     *
     * @return array<mixed>
     */
    public function filter(callable $callback) : array
    {
        return array_filter(array: $this->items, callback: $callback, mode: ARRAY_FILTER_USE_BOTH);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
