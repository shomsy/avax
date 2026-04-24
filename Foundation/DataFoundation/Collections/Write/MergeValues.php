<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Write;

/**
 * Merges arrays into collection.
 */
final readonly class MergeValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(array ...$arrays) : array
    {
        return $this->merge(...$arrays);
    }

    public function merge(array ...$arrays) : array
    {
        return array_merge($this->items, ...$arrays);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}