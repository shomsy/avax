<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\OrderedSet;

use ArrayIterator;
use Avax\DataFoundation\Collections\DataList\DataList;
use Avax\DataFoundation\Internal\Comparison\Comparator;
use Avax\DataFoundation\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Unique value collection that preserves insertion order.
 */
final readonly class OrderedSet implements IteratorAggregate, Countable
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
        $seen    = [];
        $ordered = [];

        foreach (NormalizedIterable::toArrayPreserveKeys(iterable: $items) as $item) {
            $hash = Comparator::hash(value: $item);

            if (isset($seen[$hash])) {
                continue;
            }

            $seen[$hash] = true;
            $ordered[]   = $item;
        }

        $this->items = $ordered;
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

    public function contains(mixed $value) : bool
    {
        $hash = Comparator::hash(value: $value);

        foreach ($this->items as $item) {
            if (Comparator::hash(value: $item) === $hash) {
                return true;
            }
        }

        return false;
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
        return new ArrayIterator($this->items);
    }
}
