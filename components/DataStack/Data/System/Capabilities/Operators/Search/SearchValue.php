<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Search;

/**
 * Searches for value index in collection.
 */
final readonly class SearchValue
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(mixed $value): int|false
    {
        return $this->search(value: $value);
    }

    public function search(mixed $value): int|false
    {
        $result = array_search(needle: $value, haystack: $this->items, strict: true);
        return is_int(value: $result) ? $result : false;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
