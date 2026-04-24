<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Aggregate;

use InvalidArgumentException;

/**
 * Sums numeric values by key.
 */
final readonly class SumValues
{
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param string|callable $key
     *
     * @return int|float
     */
    public function __invoke(string|callable $key) : int|float
    {
        return $this->sum(key: $key);
    }

    /**
     * @param string|callable $key
     *
     * @return int|float
     */
    public function sum(string|callable $key) : int|float
    {
        if ($this->items === []) {
            return 0;
        }

        return array_reduce(
            array   : $this->items,
            callback: fn (int|float $carry, mixed $item) : int|float => $carry + $this->extractValue(item: $item, key: $key),
            initial : 0
        );
    }

    /**
     * @param mixed           $item
     * @param string|callable $key
     *
     * @return int|float
     */
    private function extractValue(mixed $item, string|callable $key) : int|float
    {
        $value = is_callable(value: $key) ? $key($item) : ($item[$key] ?? 0);

        if (! is_numeric(value: $value)) {
            throw new InvalidArgumentException(message: 'Non-numeric value encountered in sum.');
        }

        return $value;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}