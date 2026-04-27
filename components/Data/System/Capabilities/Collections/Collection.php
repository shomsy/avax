<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;

final class Collection implements CollectionInterface, IteratorAggregate, Countable
{
    private array $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public static function from(array $items): self
    {
        return new self(items: $items);
    }

    public function all(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    public function has(int|string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function get(int|string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function first(): mixed
    {
        return reset($this->items) ?: null;
    }

    public function last(): mixed
    {
        return end($this->items) ?: null;
    }

    public function keys(): array
    {
        return array_keys($this->items);
    }

    public function values(): array
    {
        return array_values($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function map(callable $callback): self
    {
        return new self(items: array_map($callback, $this->items, array_keys($this->items)));
    }

    public function filter(callable $callback): self
    {
        return new self(items: array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }
}