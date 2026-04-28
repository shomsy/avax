<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Sequence;

use ArrayIterator;
use Avax\Components\Data\System\Capabilities\Collections\DataList\DataList;
use Avax\Components\Data\System\Capabilities\Collections\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Ordered transform-oriented sequence.
 */
final readonly class Sequence implements IteratorAggregate, Countable
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

    /**
     * @return array<int, mixed>
     */
    public function all() : array
    {
        return $this->items;
    }

    public function map(callable $callback) : self
    {
        return new self(items: array_values(array_map($callback, $this->items)));
    }

    public function filter(callable $callback) : self
    {
        return new self(items: array_values(array_filter($this->items, $callback)));
    }

    public function reduce(callable $callback, mixed $initial = null) : mixed
    {
        return array_reduce($this->items, $callback, $initial);
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
