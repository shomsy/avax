<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Order;

use InvalidArgumentException;

/**
 * Provides ordering operations: sort, reverse, shuffle, groupBy, keyBy.
 */
trait OrderTrait
{
    abstract protected function getItems(): array;

    abstract protected function withItems(array $items): static;

    public function sort(?callable $callback): static
    {
        $items = $this->getItems();

        if ($callback !== null) {
            uasort($items, $callback);
        } else {
            sort($items);
        }

        return $this->withItems($items);
    }

    public function sortDesc(?callable $callback): static
    {
        $items = $this->getItems();

        if ($callback !== null) {
            uasort($items, static fn(mixed $a, mixed $b): int => $callback($b, $a));
        } else {
            rsort($items);
        }

        return $this->withItems($items);
    }

    public function sortBy(string|callable $key, int $options = SORT_REGULAR, bool $descending = false): static
    {
        $items = $this->getItems();

        $keys = array_map(
            fn(mixed $item): mixed => is_callable($key) ? $key($item) : ($item[$key] ?? null),
            $items
        );

        if ($descending) {
            array_multisort($keys, SORT_DESC, $options, $items);
        } else {
            array_multisort($keys, SORT_ASC, $options, $items);
        }

        return $this->withItems($items);
    }

    public function reverse(): static
    {
        return $this->withItems(array_reverse($this->getItems(), true));
    }

    public function shuffle(): static
    {
        $items = $this->getItems();
        shuffle($items);

        return $this->withItems($items);
    }

    public function random(int $count = 1): static
    {
        if ($count <= 0) {
            throw new InvalidArgumentException('Count must be greater than 0.');
        }

        $items = $this->getItems();

        return $this->withItems(
            array_slice(array_rand($items, min($count, count($items))), 0, null, true)
        );
    }

    public function groupBy(string|callable $key): array
    {
        $grouped = [];

        foreach ($this->getItems() as $item) {
            $groupKey = is_callable($key) ? $key($item) : ($item[$key] ?? null);
            $grouped[$groupKey][] = $item;
        }

        return $grouped;
    }

    public function keyBy(string|callable $key): static
    {
        $keyed = [];

        foreach ($this->getItems() as $item) {
            $itemKey = is_callable($key) ? $key($item) : ($item[$key] ?? null);
            $keyed[$itemKey] = $item;
        }

        return $this->withItems($keyed);
    }

    public function partition(callable $callback): array
    {
        $pass = [];
        $fail = [];

        foreach ($this->getItems() as $item) {
            if ($callback($item)) {
                $pass[] = $item;
            } else {
                $fail[] = $item;
            }
        }

        return [$this->withItems($pass), $this->withItems($fail)];
    }

    public function nth(int $step, int $offset = 0): static
    {
        $items = [];
        $position = 0;

        foreach ($this->getItems() as $item) {
            if ($position % $step === $offset) {
                $items[] = $item;
            }
            $position++;
        }

        return $this->withItems($items);
    }
}