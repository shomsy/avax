<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Ordering;

/**
 * Sorts collection items by key.
 */
final readonly class SortValuesBy
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    /** @return array<array-key, mixed> */
    public function __invoke(string|callable $key, ?int $options = null, bool $descending = false): array
    {
        $options ??= SORT_REGULAR;

        return $this->sortBy(key: $key, options: $options, descending: $descending);
    }

    /** @return array<array-key, mixed> */
    public function sortBy(string|callable $key, ?int $options = null, bool $descending = false): array
    {
        $options ??= SORT_REGULAR;
        $items = $this->items;

        $keys = array_map(
            callback: static fn (mixed $item): mixed => is_callable(value: $key) ? $key($item) : ($item[$key] ?? null),
            array   : $items,
        );

        if ($descending) {
            array_multisort($keys, SORT_DESC, $options, $items);
        } else {
            array_multisort($keys, SORT_ASC, $options, $items);
        }

        return $items;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
