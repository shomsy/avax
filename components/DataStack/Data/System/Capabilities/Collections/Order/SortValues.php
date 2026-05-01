<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Order;

/**
 * Sorts collection items.
 */
final readonly class SortValues
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(?callable $callback = null): array
    {
        return $this->sort(callback: $callback);
    }

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

    public function getItems(): array
    {
        return $this->items;
    }
}
