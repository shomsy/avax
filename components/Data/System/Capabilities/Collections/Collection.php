<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections;

use ArrayIterator;
use Countable;
use Generator;
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

    public function flatMap(callable $callback) : self
    {
        $result = [];
        foreach ($this->items as $key => $item) {
            $mapped = $callback($item, $key);
            if (is_array($mapped)) {
                $result = array_merge($result, $mapped);
            } else {
                $result[] = $mapped;
            }
        }

        return new self(items: $result);
    }

    public function flatten() : self
    {
        $result = [];
        foreach ($this->items as $item) {
            if (is_array($item)) {
                $result = array_merge($result, iterator_to_array($this->flattenArray($item)));
            } else {
                $result[] = $item;
            }
        }

        return new self(items: $result);
    }

    private function flattenArray(array $array) : Generator
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                yield from $this->flattenArray($value);
            } else {
                yield $value;
            }
        }
    }

    public function groupBy(callable|string $callback) : array
    {
        $result = [];
        foreach ($this->items as $key => $item) {
            $groupKey            = is_callable($callback) ? $callback($item, $key) : ($item[$callback] ?? $key);
            $result[$groupKey][] = $item;
        }

        return $result;
    }

    public function keyBy(callable|string $callback) : self
    {
        $result = [];
        foreach ($this->items as $key => $item) {
            $newKey          = is_callable($callback) ? $callback($item, $key) : ($item[$callback] ?? $key);
            $result[$newKey] = $item;
        }

        return new self(items: $result);
    }

    public function sort(callable|null $callback = null) : self
    {
        $items = $this->items;
        if ($callback !== null) {
            uksort($items, $callback);
        } else {
            sort($items);
        }

        return new self(items: $items);
    }

    public function sortBy(string $key, bool $descending = false) : self
    {
        $items = $this->items;
        usort($items, function ($a, $b) use ($key, $descending) {
            $aVal = is_array($a) ? ($a[$key] ?? '') : ($a->{$key} ?? '');
            $bVal = is_array($b) ? ($b[$key] ?? '') : ($b->{$key} ?? '');

            return $descending ? $bVal <=> $aVal : $aVal <=> $bVal;
        });

        return new self(items: $items);
    }

    public function reverse() : self
    {
        return new self(items: array_reverse($this->items, preserve_keys: true));
    }

    public function shuffle() : self
    {
        $items = $this->items;
        shuffle($items);

        return new self(items: $items);
    }

    public function chunk(int $size) : array
    {
        return array_map(
            fn (array $chunk) => new self(items: $chunk),
            array_chunk($this->items, $size, preserve_keys: true)
        );
    }

    public function slice(int $offset, int|null $length = null) : self
    {
        return new self(items: array_slice($this->items, $offset, $length, preserve_keys: true));
    }

    public function take(int $limit) : self
    {
        return new self(items: array_slice($this->items, 0, $limit, preserve_keys: true));
    }

    public function skip(int $offset) : self
    {
        return new self(items: array_slice($this->items, $offset, null, preserve_keys: true));
    }

    public function unique() : self
    {
        return new self(items: array_unique($this->items, SORT_REGULAR));
    }

    public function random(int|null $count = null) : self|array
    {
        $items = $this->items;
        if ($count === null) {
            return new self(items: [array_rand($items) => $items[array_rand($items)]]);
        }

        $keys = array_rand($items, min($count, count($items)));
        $keys = is_array($keys) ? $keys : [$keys];

        return new self(items: array_intersect_key($items, array_flip($keys)));
    }

    public function where(string $key, mixed $operator, mixed $value = null) : self
    {
        if ($value === null) {
            $value    = $operator;
            $operator = '==';
        }

        return $this->filter(function ($item) use ($key, $operator, $value) {
            $itemValue = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);

            return match ($operator) {
                '=='         => $itemValue == $value,
                '==='        => $itemValue === $value,
                '!='         => $itemValue != $value,
                '!=='        => $itemValue !== $value,
                '>'          => $itemValue > $value,
                '<'          => $itemValue < $value,
                '>='         => $itemValue >= $value,
                '<='         => $itemValue <= $value,
                'contains'   => is_array($itemValue) && in_array($value, $itemValue),
                'startsWith' => is_string($itemValue) && str_starts_with($itemValue, $value),
                'endsWith'   => is_string($itemValue) && str_ends_with($itemValue, $value),
                default      => false,
            };
        });
    }

    public function whereIn(string $key, array $values) : self
    {
        return $this->filter(function ($item) use ($key, $values) {
            $itemValue = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);

            return in_array($itemValue, $values, strict: false);
        });
    }

    public function whereNull(string $key) : self
    {
        return $this->where($key, '===', null);
    }

    public function whereNotNull(string $key) : self
    {
        return $this->filter(function ($item) use ($key) {
            $itemValue = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);

            return $itemValue !== null;
        });
    }

    public function find(callable $callback) : mixed
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key)) {
                return $item;
            }
        }

        return null;
    }

    public function sum(string|null $key = null) : int|float
    {
        if ($key === null) {
            return array_sum($this->items);
        }

        return array_sum(array_map(function ($item) use ($key) {
            return is_array($item) ? ($item[$key] ?? 0) : ($item->{$key} ?? 0);
        }, $this->items));
    }

    public function avg(string|null $key = null) : float|null
    {
        $count = $this->count();
        if ($count === 0) {
            return null;
        }

        return $this->sum($key) / $count;
    }

    public function min(string|null $key = null) : mixed
    {
        if ($key === null) {
            return empty($this->items) ? null : min($this->items);
        }

        $values = array_map(function ($item) use ($key) {
            return is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
        }, $this->items);
        $values = array_filter($values, fn ($v) => $v !== null);

        return empty($values) ? null : min($values);
    }

    public function max(string|null $key = null) : mixed
    {
        if ($key === null) {
            return empty($this->items) ? null : max($this->items);
        }

        $values = array_map(function ($item) use ($key) {
            return is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
        }, $this->items);
        $values = array_filter($values, fn ($v) => $v !== null);

        return empty($values) ? null : max($values);
    }

    public function toJson(int $flags = JSON_THROW_ON_ERROR) : string
    {
        return json_encode($this->items, $flags);
    }

    public static function fromJson(string $json) : self
    {
        return new self(items: json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR));
    }

    public function merge(array|self $items) : self
    {
        $values = $items instanceof self ? $items->all() : $items;

        return new self(items: array_merge($this->items, $values));
    }

    public function union(array|self $items) : self
    {
        $values = $items instanceof self ? $items->all() : $items;

        return new self(items: $this->items + $values);
    }

    public function tap(callable $callback) : self
    {
        $callback($this);

        return $this;
    }

    public function when(bool $condition, callable $callback) : self
    {
        if ($condition) {
            return $callback($this) ?? $this;
        }

        return $this;
    }
}