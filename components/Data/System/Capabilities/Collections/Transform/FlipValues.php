<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Transform;

/**
 * Flips keys and values.
 */
final readonly class FlipValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->flip();
    }

    public function flip() : array
    {
        return array_flip(array: $this->items);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
