<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\DataList;

use ArrayIterator;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;
use Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Ordered list with sequential integer keys.
 */
final readonly class DataList implements IteratorAggregate, Countable
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

    public function append(mixed $value) : self
    {
        $items   = $this->items;
        $items[] = $value;

        return new self(items: $items);
    }

    public function prepend(mixed $value) : self
    {
        $items = $this->items;
        array_unshift($items, $value);

        return new self(items: $items);
    }

    public function map(callable $callback) : self
    {
        return new self(items: array_values(array_map($callback, $this->items)));
    }

    public function filter(callable $callback) : self
    {
        return new self(items: array_values(array_filter($this->items, $callback)));
    }

    public function first(mixed $default = null) : mixed
    {
        return $this->items[0] ?? $default;
    }

    public function last(mixed $default = null) : mixed
    {
        return $this->items === [] ? $default : $this->items[array_key_last($this->items)];
    }

    public function toCollection() : Collection
    {
        return new Collection(items: $this->items);
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
