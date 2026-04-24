<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Read;

/**
 * Reads last item from collection.
 */
final readonly class ReadLastValue
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

        return end(array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}