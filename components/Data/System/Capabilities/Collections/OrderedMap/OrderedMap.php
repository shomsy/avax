<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\OrderedMap;

use ArrayIterator;
use Avax\Components\Data\System\Capabilities\Collections\Composites\MapEntry\MapEntry;
use Avax\Components\Data\System\Capabilities\Collections\DataList\DataList;
use Avax\Components\Data\System\Capabilities\Collections\Internal\Iteration\NormalizedIterable;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Key-value map that preserves insertion order.
 */
final readonly class OrderedMap implements IteratorAggregate, Countable
{
    /**
     * @var array<array-key, mixed>
     */
    private array $items;

    /**
     * @param iterable<array-key, mixed> $items
     */
    public function __construct(
        iterable $items = [],
    )
    {
        $this->items = NormalizedIterable::toArrayPreserveKeys(iterable: $items);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function all() : array
    {
        return $this->items;
    }

    public function put(int|string $key, mixed $value) : self
    {
        $items       = $this->items;
        $items[$key] = $value;

        return new self(items: $items);
    }

    public function get(int|string $key, mixed $default = null) : mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function values() : DataList
    {
        return new DataList(items: array_values($this->items));
    }

    /**
     * @return array<int, MapEntry>
     */
    public function entries() : array
    {
        $entries = [];

        foreach ($this->items as $key => $value) {
            $entries[] = new MapEntry(key: $key, value: $value);
        }

        return $entries;
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
