<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Structures\Queue;

use ArrayIterator;
use Avax\DataFoundation\Composites\Pair\Pair;
use Avax\DataFoundation\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * First-in, first-out structure.
 */
final readonly class Queue implements IteratorAggregate, Countable
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

    public function enqueue(mixed $value) : self
    {
        $items   = $this->items;
        $items[] = $value;

        return new self(items: $items);
    }

    public function dequeue() : Pair
    {
        if ($this->items === []) {
            return new Pair(first: null, second: $this);
        }

        $items = $this->items;
        $value = array_shift($items);

        return new Pair(first: $value, second: new self(items: $items));
    }

    public function peek(mixed $default = null) : mixed
    {
        return $this->items[0] ?? $default;
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
