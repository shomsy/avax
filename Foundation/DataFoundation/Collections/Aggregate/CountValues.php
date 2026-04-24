<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Aggregate;

/**
 * Counts items in collection.
 */
final readonly class CountValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : int
    {
        return $this->count();
    }

    public function count() : int
    {
        return count(value: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}