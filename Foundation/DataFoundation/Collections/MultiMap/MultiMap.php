<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\MultiMap;

use ArrayIterator;
use Avax\DataFoundation\Collections\DataList\DataList;
use Avax\DataFoundation\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Key-value map that stores multiple values per key.
 */
final readonly class MultiMap implements IteratorAggregate, Countable
{
    /**
     * @var array<array-key, array<int, mixed>>
     */
    private array $items;

    /**
     * @param iterable<array-key, iterable<mixed>> $items
     */
    public function __construct(
        iterable $items = [],
    )
    {
        $normalized = [];

        foreach (NormalizedIterable::toArrayPreserveKeys(iterable: $items) as $key => $values) {
            $normalized[$key] = array_values(NormalizedIterable::toArrayPreserveKeys(iterable: $values));
        }

        $this->items = $normalized;
    }

    /**
     * @return array<array-key, array<int, mixed>>
     */
    public function all() : array
    {
        return $this->items;
    }

    public function put(int|string $key, mixed $value) : self
    {
        $items         = $this->items;
        $items[$key]   ??= [];
        $items[$key][] = $value;

        return new self(items: $items);
    }

    public function valuesFor(int|string $key) : DataList
    {
        return new DataList(items: $this->get(key: $key));
    }

    /**
     * @return array<int, mixed>
     */
    public function get(int|string $key) : array
    {
        return $this->items[$key] ?? [];
    }

    public function remove(int|string $key) : self
    {
        $items = $this->items;
        unset($items[$key]);

        return new self(items: $items);
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
