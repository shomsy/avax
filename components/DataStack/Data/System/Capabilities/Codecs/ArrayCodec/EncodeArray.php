<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Codecs\ArrayCodec;

/**
 * Converts collection to array.
 */
final readonly class EncodeArray
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(): array
    {
        return $this->toArray();
    }

    /** @return array<array-key, mixed> */
    public function toArray(): array
    {
        return array_map(
            callback: fn (mixed $item): mixed => $this->normalizeItem(item: $item),
            array   : $this->items,
        );
    }

    private function normalizeItem(mixed $item): mixed
    {
        if (is_object(value: $item) && method_exists(object_or_class: $item, method: 'toArray')) {
            return $item->toArray();
        }

        return $item;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
