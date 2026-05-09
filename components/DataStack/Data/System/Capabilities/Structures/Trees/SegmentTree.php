<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees;

use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Countable;
use Override;

final readonly class SegmentTree implements Countable
{
    /** @var list<int|float> */
    private array $tree;

    /**
     * @param list<int|float> $values
     */
    public function __construct(private array $values)
    {
        $size     = count(value: $values);
        $treeSize = $size * 4;

        if ($treeSize < 1) {
            $treeSize = 1;
        }

        $tree = array_fill(start_index: 0, count: $treeSize, value: 0);

        if ($size > 0) {
            $this->build(tree: $tree, node: 1, left: 0, right: $size - 1);
        }

        $this->tree = $tree;
    }

    /**
     * @param list<int|float> $tree
     */
    private function build(array &$tree, int $node, int $left, int $right) : void
    {
        if ($left === $right) {
            $tree[$node] = $this->values[$left];

            return;
        }

        $middle = intdiv(num1: $left + $right, num2: 2);
        $this->build(tree: $tree, node: $node * 2, left: $left, right: $middle);
        $this->build(tree: $tree, node: ($node * 2) + 1, left: $middle + 1, right: $right);
        $tree[$node] = $tree[$node * 2] + $tree[($node * 2) + 1];
    }

    public function rangeSum(int $from, int $to) : int|float
    {
        if ($this->values === []) {
            throw IndexOutOfBounds::at(index: $from);
        }

        $this->ensureIndex(index: $from);
        $this->ensureIndex(index: $to);

        if ($from > $to) {
            return 0;
        }

        return $this->query(node: 1, left: 0, right: count(value: $this->values) - 1, from: $from, to: $to);
    }

    private function ensureIndex(int $index) : void
    {
        if ($index < 0 || $index >= count(value: $this->values)) {
            throw IndexOutOfBounds::at(index: $index);
        }
    }

    private function query(int $node, int $left, int $right, int $from, int $to) : int|float
    {
        if ($to < $left || $right < $from) {
            return 0;
        }

        if ($from <= $left && $right <= $to) {
            return $this->tree[$node];
        }

        $middle = intdiv(num1: $left + $right, num2: 2);

        return $this->query(node: $node * 2, left: $left, right: $middle, from: $from, to: $to)
            + $this->query(node: ($node * 2) + 1, left: $middle + 1, right: $right, from: $from, to: $to);
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->values);
    }
}
