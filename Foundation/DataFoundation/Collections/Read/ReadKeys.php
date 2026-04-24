<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Read;

/**
 * Reads all keys from collection.
 */
final readonly class ReadKeys
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->get();
    }

    public function get() : array
    {
        return array_keys(array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}