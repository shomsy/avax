<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Write;

/**
 * Prepends a value to the start of collection.
 */
final readonly class PrependValue
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(mixed $value) : array
    {
        return $this->prepend(value: $value);
    }

    public function prepend(mixed $value) : array
    {
        return array_merge([$value], $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}