<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Strings;

/**
 * Converts values to uppercase.
 */
final readonly class UppercaseValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->uppercase();
    }

    public function uppercase() : array
    {
        return array_map(callback: 'strtoupper', array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}