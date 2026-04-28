<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Order;

/**
 * Reverses collection order.
 */
final readonly class ReverseValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->reverse();
    }

    public function reverse() : array
    {
        return array_reverse(array: $this->items, preserve_keys: true);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
