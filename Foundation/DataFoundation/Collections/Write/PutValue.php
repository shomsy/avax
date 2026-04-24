<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Write;

/**
 * Puts a value by key into collection items.
 */
final readonly class PutValue
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $key, mixed $value) : array
    {
        return $this->put(key: $key, value: $value);
    }

    public function put(string $key, mixed $value) : array
    {
        $items       = $this->items;
        $items[$key] = $value;

        return $items;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}