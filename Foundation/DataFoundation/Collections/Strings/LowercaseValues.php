<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Strings;

/**
 * Converts values to lowercase.
 */
final readonly class LowercaseValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->lowercase();
    }

    public function lowercase() : array
    {
        return array_map(callback: 'strtolower', array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}