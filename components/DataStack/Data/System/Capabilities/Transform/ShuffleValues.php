<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Transform;

/**
 * Shuffles collection order randomly.
 */
final readonly class ShuffleValues
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    /** @return array<array-key, mixed> */
    public function __invoke(): array
    {
        return $this->shuffle();
    }

    /** @return array<array-key, mixed> */
    public function shuffle(): array
    {
        $items = $this->items;
        shuffle(array: $items);

        return $items;
    }

    /** @return array<array-key, mixed> */
    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
