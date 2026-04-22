<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Search;

use InvalidArgumentException;

/**
 * Provides exact match search operations: contains, search, where, whereIn.
 */
trait SearchTrait
{
    abstract protected function getItems(): array;

    abstract protected function withItems(array $items): static;

    public function contains(mixed $value): bool
    {
        return in_array($value, $this->getItems(), true);
    }

    public function search(mixed $value): int|false
    {
        return array_search($value, $this->getItems(), true);
    }

    public function indexOf(mixed $value): int|false
    {
        return $this->search($value);
    }

    public function lastIndexOf(mixed $value): int|false
    {
        $reversed = array_reverse($this->getItems(), true);

        return array_search($value, $reversed, true);
    }

    public function where(string $key, mixed $value): static
    {
        $filtered = array_filter(
            $this->getItems(),
            static fn(mixed $item): bool => ($item[$key] ?? null) === $value
        );

        return $this->withItems($filtered);
    }

    public function whereNot(string $key, mixed $value): static
    {
        $filtered = array_filter(
            $this->getItems(),
            static fn(mixed $item): bool => ($item[$key] ?? null) !== $value
        );

        return $this->withItems($filtered);
    }

    public function whereIn(string $key, array $values): static
    {
        if ($values === []) {
            throw new InvalidArgumentException('Values array cannot be empty.');
        }

        $filtered = array_filter(
            $this->getItems(),
            static fn(mixed $item): bool => in_array($item[$key] ?? null, $values, true)
        );

        return $this->withItems($filtered);
    }

    public function whereNotIn(string $key, array $values): static
    {
        $filtered = array_filter(
            $this->getItems(),
            static fn(mixed $item): bool => ! in_array($item[$key] ?? null, $values, true)
        );

        return $this->withItems($filtered);
    }

    public function whereBetween(string $key, array $range): static
    {
        if (count($range) !== 2) {
            throw new InvalidArgumentException('Range array must contain exactly two elements.');
        }

        [$min, $max] = $range;

        $filtered = array_filter(
            $this->getItems(),
            static fn(mixed $item): bool => ($item[$key] ?? null) >= $min
                && ($item[$key] ?? null) <= $max
        );

        return $this->withItems($filtered);
    }

    public function whereNull(string $key): static
    {
        return $this->where($key, null);
    }

    public function whereNotNull(string $key): static
    {
        return $this->whereNot($key, null);
    }

    public function some(callable $callback): bool
    {
        foreach ($this->getItems() as $item) {
            if ($callback($item)) {
                return true;
            }
        }

        return false;
    }

    public function every(callable $callback): bool
    {
        foreach ($this->getItems() as $item) {
            if (! $callback($item)) {
                return false;
            }
        }

        return true;
    }

    public function none(callable $callback): bool
    {
        return ! $this->some($callback);
    }
}