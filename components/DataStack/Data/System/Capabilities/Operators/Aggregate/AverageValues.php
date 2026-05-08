<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Aggregate;

/**
 * Calculates average of numeric values by key.
 */
final readonly class AverageValues
{
    /** @param array<array-key, mixed> $items */
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(string|callable $key): float
    {
        return $this->average(key: $key);
    }

    public function average(string|callable $key): float
    {
        $count = count(value: $this->items);

        return $count !== 0
            ? new SumValues(items: $this->items)->sum(key: $key) / $count
            : 0.0;
    }

    /** @return array<array-key, mixed> */
    public function getItems(): array
    {
        return $this->items;
    }
}
