<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Structures\PriorityQueue;

use ArrayIterator;
use Avax\DataFoundation\Composites\Pair\Pair;
use Avax\DataFoundation\Internal\Comparison\Ordering;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Queue ordered by numeric priority.
 */
final readonly class PriorityQueue implements IteratorAggregate, Countable
{
    /**
     * @var array<int, array{priority: int, value: mixed}>
     */
    private array $items;

    /**
     * @param array<int, array{priority: int, value: mixed}> $items
     */
    public function __construct(
        array $items = [],
    )
    {
        $sorted = $items;
        usort(
            $sorted,
            static fn (array $left, array $right) : int => $right['priority'] <=> $left['priority']
        );

        $this->items = $sorted;
    }

    public function push(mixed $value, int $priority = 0) : self
    {
        $items   = $this->items;
        $items[] = ['priority' => $priority, 'value' => $value];

        return new self(items: $items);
    }

    public function pull() : Pair
    {
        if ($this->items === []) {
            return new Pair(first: null, second: $this);
        }

        $items = $this->items;
        $entry = array_shift($items);

        return new Pair(first: $entry['value'], second: new self(items: $items));
    }

    /**
     * @return array<int, array{priority: int, value: mixed}>
     */
    public function all() : array
    {
        return $this->items;
    }

    public function ordering() : Ordering
    {
        return Ordering::Descending;
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
