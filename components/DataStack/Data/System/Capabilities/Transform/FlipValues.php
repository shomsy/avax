<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Transform;

/**
 * Flips keys and values.
 */
final readonly class FlipValues
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
        return $this->flip();
    }

    /** @return array<array-key, mixed> */
    public function flip(): array
    {
        return array_flip(array: $this->items);
    }

    /** @return array<array-key, mixed> */
    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
