<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Structures\Deque;

use ArrayIterator;
use Avax\DataFoundation\Composites\Pair\Pair;
use Avax\DataFoundation\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Double-ended queue.
 */
final readonly class Deque implements IteratorAggregate, Countable
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
        $this->items = array_values(NormalizedIterable::toArrayPreserveKeys(iterable: $items));
    }

    public function pushFront(mixed $value) : self
    {
        $items = $this->items;
        array_unshift($items, $value);

        return new self(items: $items);
    }

    public function pushBack(mixed $value) : self
    {
        $items   = $this->items;
        $items[] = $value;

        return new self(items: $items);
    }

    public function popFront() : Pair
    {
        if ($this->items === []) {
            return new Pair(first: null, second: $this);
        }

        $items = $this->items;
        $value = array_shift($items);

        return new Pair(first: $value, second: new self(items: $items));
    }

    public function popBack() : Pair
    {
        if ($this->items === []) {
            return new Pair(first: null, second: $this);
        }

        $items = $this->items;
        $value = array_pop($items);

        return new Pair(first: $value, second: new self(items: $items));
    }

    /**
     * @return array<int, mixed>
     */
    public function all() : array
    {
        return $this->items;
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
