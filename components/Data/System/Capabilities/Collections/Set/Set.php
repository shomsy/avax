<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Set;

use ArrayIterator;
use Avax\Components\Data\System\Capabilities\Collections\DataList\DataList;
use Avax\Components\Data\System\Capabilities\Collections\Internal\Comparison\Comparator;
use Avax\Components\Data\System\Capabilities\Collections\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use JsonException;
use Traversable;

/**
 * Unordered unique value collection.
 */
final readonly class Set implements IteratorAggregate, Countable
{
    /**
     * @var array<int, mixed>
     */
    private array $items;

    /**
     * @param iterable<mixed> $items
     */
    public function __construct(
        iterable $items = [],
    )
    {
        $this->items = self::normalize(items: $items);
    }

    /**
     * @param iterable<mixed> $items
     *
     * @return array<int, mixed>
     */
    private static function normalize(iterable $items) : array
    {
        $normalized = [];
        $seen       = [];

        foreach (NormalizedIterable::toArrayPreserveKeys(iterable: $items) as $item) {
            try {
                $hash = Comparator::hash(value: $item);
            } catch (JsonException) {
                $hash = serialize($item);
            }

            if (isset($seen[$hash])) {
                continue;
            }

            $seen[$hash]  = true;
            $normalized[] = $item;
        }

        return $normalized;
    }

    /**
     * @return array<int, mixed>
     */
    public function all() : array
    {
        return $this->items;
    }

    public function add(mixed $value) : self
    {
        return new self(items: [...$this->items, $value]);
    }

    public function remove(mixed $value) : self
    {
        try {
            $hash = Comparator::hash(value: $value);
        } catch (JsonException) {
            $hash = serialize($value);
        }

        $filtered = array_values(array_filter(
                                     $this->items,
                                     static function (mixed $item) use ($hash) : bool {
                                         try {
                                             return Comparator::hash(value: $item) !== $hash;
                                         } catch (JsonException) {
                                             return serialize($item) !== $hash;
                                         }
                                     }
                                 ));

        return new self(items: $filtered);
    }

    public function union(iterable $items) : self
    {
        return new self(items: [...$this->items, ...NormalizedIterable::toArrayPreserveKeys(iterable: $items)]);
    }

    public function intersect(iterable $items) : self
    {
        $other = new self(items: $items);

        return new self(items: array_values(array_filter(
                                                $this->items,
                                                static fn (mixed $item) : bool => $other->contains(value: $item)
                                            )));
    }

    public function contains(mixed $value) : bool
    {
        try {
            $hash = Comparator::hash(value: $value);
        } catch (JsonException) {
            $hash = serialize($value);
        }

        foreach ($this->items as $item) {
            try {
                $itemHash = Comparator::hash(value: $item);
            } catch (JsonException) {
                $itemHash = serialize($item);
            }

            if ($itemHash === $hash) {
                return true;
            }
        }

        return false;
    }

    public function diff(iterable $items) : self
    {
        $other = new self(items: $items);

        return new self(items: array_values(array_filter(
                                                $this->items,
                                                static fn (mixed $item) : bool => ! $other->contains(value: $item)
                                            )));
    }

    public function toDataList() : DataList
    {
        return new DataList(items: $this->items);
    }

    public function count() : int
    {
        return count($this->items);
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator(array: $this->items);
    }
}
