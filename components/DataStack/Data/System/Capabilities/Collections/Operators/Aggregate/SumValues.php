<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Aggregate;

/**
 * Calculates sum of numeric values by key.
 */
final readonly class SumValues
{
    public function __construct(
        private array $items = [],
    ) {
    }

    public function __invoke(string|callable $key): float|int
    {
        return $this->sum(key: $key);
    }

    public function sum(string|callable $key): float|int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $value = is_callable($key) ? $key($item) : ($item[$key] ?? 0);
            $total += $value;
        }

        return $total;
    }
}
