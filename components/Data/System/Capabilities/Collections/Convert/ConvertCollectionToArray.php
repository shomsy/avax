<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Convert;

/**
 * Converts collection to array.
 */
final readonly class ConvertCollectionToArray
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke() : array
    {
        return $this->toArray();
    }

    public function toArray() : array
    {
        return array_map(
            callback: fn (mixed $item) : mixed => $this->normalizeItem(item: $item),
            array   : $this->items
        );
    }

    private function normalizeItem(mixed $item) : mixed
    {
        if (is_object(value: $item) && method_exists(object_or_class: $item, method: 'toArray')) {
            return $item->toArray();
        }

        return $item;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
