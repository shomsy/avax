<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Order;

/**
 * Sorts collection items by key.
 */
final readonly class SortValuesBy
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string|callable $key, int $options = SORT_REGULAR, bool $descending = false) : array
    {
        return $this->sortBy(key: $key, options: $options, descending: $descending);
    }

    public function sortBy(string|callable $key, int $options = SORT_REGULAR, bool $descending = false) : array
    {
        $items = $this->items;

        $keys = array_map(
            callback: fn (mixed $item) : mixed => is_callable(value: $key) ? $key($item) : ($item[$key] ?? null),
            array   : $items
        );

        if ($descending) {
            array_multisort($keys, SORT_DESC, $options, $items);
        } else {
            array_multisort($keys, SORT_ASC, $options, $items);
        }

        return $items;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}