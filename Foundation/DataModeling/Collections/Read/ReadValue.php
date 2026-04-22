<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Read;

/**
 * Reads a value by key from collection items.
 */
final readonly class ReadValue
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $key, mixed $default = null) : mixed
    {
        return $this->get(key: $key, default: $default);
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        if (array_key_exists(key: $key, array: $this->items)) {
            return $this->items[$key];
        }

        return $default;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}