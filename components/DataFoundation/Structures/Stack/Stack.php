<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Structures\Stack;

use ArrayIterator;
use Avax\DataFoundation\Composites\Pair\Pair;
use Avax\DataFoundation\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Last-in, first-out structure.
 */
final readonly class Stack implements IteratorAggregate, Countable
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

    public function push(mixed $value) : self
    {
        $items   = $this->items;
        $items[] = $value;

        return new self(items: $items);
    }

    public function pop() : Pair
    {
        if ($this->items === []) {
            return new Pair(first: null, second: $this);
        }

        $items = $this->items;
        $value = array_pop($items);

        return new Pair(first: $value, second: new self(items: $items));
    }

    public function peek(mixed $default = null) : mixed
    {
        return $this->items === [] ? $default : $this->items[array_key_last($this->items)];
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
