<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Order;

/**
 * Shuffles collection order randomly.
 */
final readonly class ShuffleValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->shuffle();
    }

    public function shuffle() : array
    {
        $items = $this->items;
        shuffle(array: $items);

        return $items;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}