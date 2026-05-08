<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Transform;

/**
 * Removes duplicate values.
 */
final readonly class UniqueValues
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
        return $this->unique();
    }

    /** @return array<array-key, mixed> */
    public function unique(): array
    {
        return array_unique(array: $this->items, flags: SORT_REGULAR);
    }

    /** @return array<array-key, mixed> */
    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
