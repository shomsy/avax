<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Search;

/**
 * Searches for value index in collection.
 */
final readonly class SearchValue
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(mixed $value) : int|false
    {
        return $this->search(value: $value);
    }

    public function search(mixed $value) : int|false
    {
        return array_search(needle: $value, haystack: $this->items, strict: true);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}