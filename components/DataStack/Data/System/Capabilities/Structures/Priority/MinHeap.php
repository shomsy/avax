<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\HeapStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\EmptyStructure;
use Countable;
use Override;

/**
 * MinHeap — a binary heap that extracts the smallest priority first.
 */
final readonly class MinHeap implements Countable, HeapStructure
{
    /**
     * @param list<array{value: mixed, priority: int|float}> $nodes
     */
    private function __construct(private array $nodes = []) {}

    public static function empty() : self
    {
        return new self();
    }

    /**
     * @param iterable<array{value: mixed, priority: int|float}> $items
     */
    public static function from(iterable $items) : self
    {
        $heap = new self();
        foreach ($items as $item) {
            $heap = $heap->insert(value: $item['value'], priority: $item['priority']);
        }

        return $heap;
    }

    #[Override]
    public function insert(mixed $value, int|float $priority) : self
    {
        $nodes = [...$this->nodes, ['value' => $value, 'priority' => $priority]];
        $index = count(value: $nodes) - 1;

        while ($index > 0) {
            $parent = intdiv(num1: $index - 1, num2: 2);
            if ($nodes[$parent]['priority'] <= $nodes[$index]['priority']) {
                break;
            }
            [$nodes[$parent], $nodes[$index]] = [$nodes[$index], $nodes[$parent]];
            $index = $parent;
        }

        return new self(nodes: $nodes);
    }

    #[Override]
    public function peek(mixed $default = null) : mixed
    {
        return $this->nodes[0]['value'] ?? $default;
    }

    #[Override]
    public function extract() : mixed
    {
        if ($this->nodes === []) {
            throw EmptyStructure::forOperation(operation: 'extract');
        }

        return $this->nodes[0]['value'];
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->nodes === [];
    }

    public function removeRoot() : self
    {
        if ($this->nodes === []) {
            throw EmptyStructure::forOperation(operation: 'remove root');
        }

        $nodes = $this->nodes;
        $last = array_pop(array: $nodes);

        if ($nodes === []) {
            return new self();
        }

        $nodes[0] = $last;
        $this->siftDown(nodes: $nodes, index: 0);

        return new self(nodes: $nodes);
    }

    /**
     * @param list<array{value: mixed, priority: int|float}> $nodes
     */
    private function siftDown(array &$nodes, int $index) : void
    {
        $count = count(value: $nodes);
        while (true) {
            $left = ($index * 2) + 1;
            $right = $left + 1;
            $smallest = $index;

            if ($left < $count && $nodes[$left]['priority'] < $nodes[$smallest]['priority']) {
                $smallest = $left;
            }
            if ($right < $count && $nodes[$right]['priority'] < $nodes[$smallest]['priority']) {
                $smallest = $right;
            }
            if ($smallest === $index) {
                break;
            }
            [$nodes[$index], $nodes[$smallest]] = [$nodes[$smallest], $nodes[$index]];
            $index = $smallest;
        }
    }

    /**
     * @return list<mixed>
     */
    public function valuesInPriorityOrder() : array
    {
        $heap = $this;
        $values = [];
        while (! $heap->isEmpty()) {
            $values[] = $heap->extract();
            $heap = $heap->removeRoot();
        }

        return $values;
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->nodes);
    }
}
