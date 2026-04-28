<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Aggregate;

/**
 * Calculates average of numeric values by key.
 */
final readonly class AverageValues
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param string|callable $key
     *
     * @return float
     */
    public function __invoke(string|callable $key) : float
    {
        return $this->average(key: $key);
    }

    /**
     * @param string|callable $key
     *
     * @return float
     */
    public function average(string|callable $key) : float
    {
        $count = count(value: $this->items);

        return $count !== 0
            ? new SumValues(items: $this->items)->sum(key: $key) / $count
            : 0.0;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}
