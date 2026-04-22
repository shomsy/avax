<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Aggregation;

use InvalidArgumentException;
use LogicException;

/**
 * Provides aggregation operations: sum, average, min, max, reduce.
 */
trait AggregationTrait
{
    abstract protected function getItems(): array;

    public function sum(string|callable $key): int|float
    {
        $items = $this->getItems();
        $this->validateNonEmpty($items);

        return array_reduce(
            $items,
            static fn(int|float $carry, mixed $item) => $carry + $this->extractValue($item, $key),
            0
        );
    }

    public function average(string|callable $key): float
    {
        $items = $this->getItems();
        $count = count($items);

        return $count !== 0
            ? $this->sum($key) / $count
            : 0.0;
    }

    public function min(string|callable $key): mixed
    {
        $items = $this->getItems();
        $this->validateNonEmpty($items);

        $values = array_map(
            static fn(mixed $item): mixed => $this->extractValue($item, $key),
            $items
        );

        return min($values);
    }

    public function max(string|callable $key): mixed
    {
        $items = $this->getItems();
        $this->validateNonEmpty($items);

        $values = array_map(
            static fn(mixed $item): mixed => $this->extractValue($item, $key),
            $items
        );

        return max($values);
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $this->validateNonEmpty($this->getItems());

        return array_reduce($this->getItems(), $callback, $initial);
    }

    public function countBy(string|callable $key): array
    {
        $this->validateNonEmpty($this->getItems());

        $result = [];

        foreach ($this->getItems() as $item) {
            $value = is_callable($key) ? $key($item) : ($item[$key] ?? null);
            $result[$value] = ($result[$value] ?? 0) + 1;
        }

        return $result;
    }

    public function aggregateGroupBy(string|callable $key): array
    {
        $this->validateNonEmpty($this->getItems());

        $grouped = [];

        foreach ($this->getItems() as $item) {
            $groupKey = is_callable($key) ? $key($item) : ($item[$key] ?? null);
            $grouped[$groupKey][] = $item;
        }

        return $grouped;
    }

    private function extractValue(mixed $item, string|callable $key): int|float
    {
        $value = is_callable($key) ? $key($item) : ($item[$key] ?? 0);

        if (! is_numeric($value)) {
            throw new InvalidArgumentException('Non-numeric value encountered in aggregation.');
        }

        return $value;
    }

    private function validateNonEmpty(array $items): void
    {
        if ($items === []) {
            throw new LogicException('Cannot perform aggregation on empty collection.');
        }
    }
}