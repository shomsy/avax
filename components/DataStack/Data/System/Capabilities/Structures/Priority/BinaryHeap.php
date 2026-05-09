<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\HeapStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\EmptyStructure;
use Countable;
use Override;

final readonly class BinaryHeap implements Countable, HeapStructure
{
    /**
     * @param list<array{value: mixed, priority: int|float}> $nodes
     */
    private function __construct(private array $nodes = [], private bool $extractsMinimum = true) {}

    public static function min() : self
    {
        return new self(extractsMinimum: true);
    }

    public static function max() : self
    {
        return new self(extractsMinimum: false);
    }

    #[Override]
    public function insert(mixed $value, int|float $priority) : self
    {
        $nodes = [...$this->nodes, ['value' => $value, 'priority' => $priority]];
        $index = count(value: $nodes) - 1;

        while ( $index > 0 ) {
            $parent = intdiv(num1: $index - 1, num2: 2);

            if ($this->isOrdered(parentPriority: $nodes[$parent]['priority'], childPriority: $nodes[$index]['priority'])) {
                break;
            }

            [$nodes[$parent], $nodes[$index]] = [$nodes[$index], $nodes[$parent]];
            $index = $parent;
        }

        return new self(nodes: $nodes, extractsMinimum: $this->extractsMinimum);
    }

    private function isOrdered(int|float $parentPriority, int|float $childPriority) : bool
    {
        if ($this->extractsMinimum) {
            return $parentPriority <= $childPriority;
        }

        return $parentPriority >= $childPriority;
    }

    #[Override]
    public function peek(mixed $default = null) : mixed
    {
        return $this->nodes[0]['value'] ?? $default;
    }

    /**
     * @return list<mixed>
     */
    public function valuesInPriorityOrder() : array
    {
        $heap   = $this;
        $values = [];

        while ( ! $heap->isEmpty() ) {
            $values[] = $heap->extract();
            $heap     = $heap->removeRoot();
        }

        return $values;
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->nodes === [];
    }

    #[Override]
    public function extract() : mixed
    {
        if ($this->nodes === []) {
            throw EmptyStructure::forOperation(operation: 'extract');
        }

        return $this->nodes[0]['value'];
    }

    public function removeRoot() : self
    {
        if ($this->nodes === []) {
            throw EmptyStructure::forOperation(operation: 'remove root');
        }

        $nodes = $this->nodes;
        $last  = array_pop(array: $nodes);

        if ($nodes === []) {
            return new self(extractsMinimum: $this->extractsMinimum);
        }

        $nodes[0] = $last;
        $index    = 0;

        while ( true ) {
            $left   = ($index * 2) + 1;
            $right  = $left + 1;
            $target = $index;

            if (isset($nodes[$left]) && ! $this->isOrdered(parentPriority: $nodes[$target]['priority'], childPriority: $nodes[$left]['priority'])) {
                $target = $left;
            }

            if (isset($nodes[$right]) && ! $this->isOrdered(parentPriority: $nodes[$target]['priority'], childPriority: $nodes[$right]['priority'])) {
                $target = $right;
            }

            if ($target === $index) {
                break;
            }

            [$nodes[$index], $nodes[$target]] = [$nodes[$target], $nodes[$index]];
            $index = $target;
        }

        return new self(nodes: $nodes, extractsMinimum: $this->extractsMinimum);
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->nodes);
    }
}
