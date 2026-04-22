<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Strings;

/**
 * Trims whitespace from values.
 */
final readonly class TrimValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->trim();
    }

    public function trim() : array
    {
        return array_map(callback: 'trim', array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}