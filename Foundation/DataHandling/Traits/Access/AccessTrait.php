<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Access;

use ArrayIterator;
use Closure;
use Countable;
use InvalidArgumentException;
use Traversable;

/**
 * Provides array-like access and iteration capabilities.
 */
trait AccessTrait
{
    abstract protected function getItems(): array;

    abstract protected function withItems(array $items): static;

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->getItems()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getItems()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $items = $this->getItems();

        if ($offset === null) {
            $items[] = $value;
        } else {
            $items[$offset] = $value;
        }

        $this->withItems($items);
    }

    public function offsetUnset(mixed $offset): void
    {
        $items = $this->getItems();
        unset($items[$offset]);
        $this->withItems($items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->getItems());
    }

    public function count(): int
    {
        return count($this->getItems());
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }

    public function keys(): array
    {
        return array_keys($this->getItems());
    }

    public function values(): array
    {
        return array_values($this->getItems());
    }

    public function flip(): static
    {
        return $this->withItems(array_flip($this->getItems()));
    }

    public function getMultiple(array $keys): array
    {
        $items = $this->getItems();

        return array_intersect_key($items, array_flip($keys));
    }

    public function setMultiple(array $values): static
    {
        $items = $this->getItems();

        foreach ($values as $key => $value) {
            $items[$key] = $value;
        }

        return $this->withItems($items);
    }

    public function pull(mixed $offset): mixed
    {
        $items = $this->getItems();
        $value = $items[$offset] ?? null;

        if (array_key_exists($offset, $items)) {
            unset($items[$offset]);
            $this->withItems($items);
        }

        return $value;
    }

    public function first(mixed $default = null): mixed
    {
        $items = $this->getItems();

        if ($items === []) {
            return $default;
        }

        return reset($items);
    }

    public function last(mixed $default = null): mixed
    {
        $items = $this->getItems();

        if ($items === []) {
            return $default;
        }

        return end($items);
    }
}