<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Ordering;

/**
 * Sorts collection items.
 */
final readonly class SortValues
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(?callable $callback = null): array
    {
        return $this->sort(callback: $callback);
    }

    /** @return array<array-key, mixed> */
    public function sort(?callable $callback = null): array
    {
        $items = $this->items;

        if ($callback !== null) {
            uasort(array: $items, callback: $callback);
        } else {
            sort(array: $items);
        }

        return $items;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
