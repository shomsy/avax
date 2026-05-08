<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Transform;

/**
 * Reverses collection order.
 */
final readonly class ReverseValues
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
        return $this->reverse();
    }

    /** @return array<array-key, mixed> */
    public function reverse(): array
    {
        return array_reverse(array: $this->items, preserve_keys: true);
    }

    /** @return array<array-key, mixed> */
    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
