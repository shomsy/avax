<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\LinearStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Countable;
use Override;

/**
 * DynamicArray — an indexed sequence with append growth.
 *
 * Unlike fixed-size arrays, this grows automatically when elements are appended.
 * Uses PHP arrays internally (which already have dynamic growth), but exposes
 * the dynamic array semantics explicitly.
 */
final readonly class DynamicArray implements Countable, LinearStructure
{
    /** @var array<int, mixed> */
    private array $items;
    private int $capacity;

    /**
     * @param array<int, mixed> $items
     */
    public function __construct(array $items = [], ?int $capacity = null)
    {
        $this->items = array_values(array: $items);
        $this->capacity = $capacity ?? max(1, count(value: $items));
    }

    public static function empty(int $initialCapacity = 16) : self
    {
        return new self(items: [], capacity: $initialCapacity);
    }

    /**
     * @param iterable<mixed> $items
     */
    public static function from(iterable $items) : self
    {
        $arr = array_values(is_array(value: $items) ? $items : iterator_to_array(iterator: $items));

        return new self(items: $arr, capacity: max(16, count(value: $arr)));
    }

    public function append(mixed $value) : self
    {
        $items = [...$this->items, $value];
        $capacity = $this->capacity;

        if (count(value: $items) > $capacity) {
            $capacity = $capacity * 2;
        }

        return new self(items: $items, capacity: $capacity);
    }

    public function put(int $index, mixed $value) : self
    {
        if ($index < 0) {
            throw InvalidCapacity::because(reason: 'DynamicArray index cannot be negative.');
        }

        $items = $this->items;

        if ($index >= count(value: $items)) {
            $items = [...$items, ...array_fill(start_index: 0, count: $index - count(value: $items) + 1, value: null)];
        }

        $items[$index] = $value;

        return new self(items: $items, capacity: $this->capacity);
    }

    public function get(int $index, mixed $default = null) : mixed
    {
        return $this->items[$index] ?? $default;
    }

    public function removeAt(int $index) : self
    {
        if ($index < 0 || $index >= count(value: $this->items)) {
            return $this;
        }

        $items = $this->items;
        array_splice(array: $items, offset: $index, length: 1);

        return new self(items: $items, capacity: $this->capacity);
    }

    /**
     * @return list<mixed>
     */
    public function toArray() : array
    {
        return $this->items;
    }

    #[Override]
    public function first(mixed $default = null) : mixed
    {
        return $this->items[0] ?? $default;
    }

    #[Override]
    public function last(mixed $default = null) : mixed
    {
        return $this->items === [] ? $default : $this->items[array_key_last(array: $this->items)];
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->items === [];
    }

    public function length() : int
    {
        return count(value: $this->items);
    }

    public function getCapacity() : int
    {
        return $this->capacity;
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->items);
    }
}
