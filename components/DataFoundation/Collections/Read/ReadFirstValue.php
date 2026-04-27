<?php

declare(strict_types=1);

namespace components\DataFoundation\Collections\Read;

/**
 * Reads first item from collection.
 */
final readonly class ReadFirstValue
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(mixed $default = null) : mixed
    {
        return $this->get(default: $default);
    }

    public function get(mixed $default = null) : mixed
    {
        if ($this->items === []) {
            return $default;
        }

        return reset(array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}