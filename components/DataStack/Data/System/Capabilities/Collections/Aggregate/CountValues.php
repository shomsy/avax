<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Aggregate;

/**
 * Counts the number of items in the collection.
 */
final readonly class CountValues
{
    /**
     * @param array<mixed> $items
     */
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

    /**
     * @return array<mixed>
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
