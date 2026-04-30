<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Transform;

/**
 * Reduces collection to a single value.
 */
final readonly class ReduceValues
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param callable $callback fn(mixed $carry, mixed $item, int|string $key): mixed
     *
     */
    public function __invoke(callable $callback, mixed $initial = null) : mixed
    {
        return $this->reduce(callback: $callback, initial: $initial);
    }

    /**
     * @param callable $callback fn(mixed $carry, mixed $item, int|string $key): mixed
     *
     */
    public function reduce(callable $callback, mixed $initial = null) : mixed
    {
        return array_reduce(array: $this->items, callback: $callback, initial: $initial);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
