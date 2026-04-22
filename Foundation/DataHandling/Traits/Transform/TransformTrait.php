<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Transform;

use Closure;
use InvalidArgumentException;

/**
 * Provides transformation operations: map, filter, pluck, zip, tap.
 */
trait TransformTrait
{
    abstract protected function getItems(): array;

    abstract protected function withItems(array $items): static;

    public function map(callable $callback): static
    {
        return $this->withItems(array_map($callback, $this->getItems()));
    }

    public function filter(callable $callback): static
    {
        return $this->withItems(
            array_filter($this->getItems(), $callback, ARRAY_FILTER_USE_BOTH)
        );
    }

    public function flatMap(callable $callback): static
    {
        $mapped = [];

        foreach ($this->getItems() as $key => $item) {
            $result = $callback($item, $key);

            if (is_array($result)) {
                foreach ($result as $value) {
                    $mapped[] = $value;
                }
            } else {
                $mapped[] = $result;
            }
        }

        return $this->withItems($mapped);
    }

    public function pluck(string|callable $key): array
    {
        return array_map(
            fn(mixed $item): mixed => is_callable($key) ? $key($item) : ($item[$key] ?? null),
            $this->getItems()
        );
    }

    public function zip(array ...$items): static
    {
        if ($items === []) {
            throw new InvalidArgumentException('At least one array must be provided.');
        }

        return $this->withItems(array_map(null, ...[$this->getItems(), ...$items]));
    }

    public function chunk(int $size): static
    {
        if ($size <= 0) {
            throw new InvalidArgumentException('Chunk size must be greater than 0.');
        }

        return $this->withItems(array_chunk($this->getItems(), $size, true));
    }

    public function pad(int $length, mixed $value = null): static
    {
        return $this->withItems(array_pad($this->getItems(), $length, $value));
    }

    public function flatten(int $depth = -1): static
    {
        return $this->withItems(array_flatten($this->getItems(), $depth));
    }

    public function collapse(): static
    {
        $collapsed = [];

        foreach ($this->getItems() as $item) {
            if (is_array($item)) {
                foreach ($item as $value) {
                    $collapsed[] = $value;
                }
            } else {
                $collapsed[] = $item;
            }
        }

        return $this->withItems($collapsed);
    }

    public function unique(): static
    {
        return $this->withItems(array_unique($this->getItems(), SORT_REGULAR));
    }

    public function values(): static
    {
        return $this->withItems(array_values($this->getItems()));
    }

    public function keys(): static
    {
        return $this->withItems(array_keys($this->getItems()));
    }

    public function tap(callable $callback): static
    {
        $callback($this);

        return $this;
    }

    public function when(bool $condition, callable $callback): static
    {
        if ($condition) {
            return $callback($this) ?? $this;
        }

        return $this;
    }

    public function unless(bool $condition, callable $callback): static
    {
        return $this->when(! $condition, $callback);
    }

    public function transform(callable $callback): static
    {
        $items = $this->getItems();

        foreach ($items as $key => $item) {
            $items[$key] = $callback($item, $key);
        }

        return $this->withItems($items);
    }
}