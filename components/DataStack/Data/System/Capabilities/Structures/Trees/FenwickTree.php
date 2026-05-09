<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees;

use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Countable;
use Override;

final readonly class FenwickTree implements Countable
{
    /** @var array<int, int|float> */
    private array $tree;

    /**
     * @param array<int, int|float> $tree
     */
    private function __construct(private int $size, array $tree)
    {
        $this->tree = $tree;
    }

    public static function withSize(int $size) : self
    {
        if ($size < 1) {
            return new self(size: 0, tree: [0]);
        }

        return new self(size: $size, tree: array_fill(start_index: 0, count: $size + 1, value: 0));
    }

    public function add(int $index, int|float $delta) : self
    {
        $this->ensureIndex(index: $index);

        $tree    = $this->tree;
        $current = $index + 1;

        while ( $current <= $this->size ) {
            $tree[$current] += $delta;
            $current        += $current & -$current;
        }

        return new self(size: $this->size, tree: $tree);
    }

    private function ensureIndex(int $index) : void
    {
        if ($index < 0 || $index >= $this->size) {
            throw IndexOutOfBounds::at(index: $index);
        }
    }

    public function rangeSum(int $from, int $to) : int|float
    {
        $this->ensureIndex(index: $from);
        $this->ensureIndex(index: $to);

        if ($from > $to) {
            return 0;
        }

        return $this->prefixSum(index: $to) - ($from === 0 ? 0 : $this->prefixSum(index: $from - 1));
    }

    public function prefixSum(int $index) : int|float
    {
        $this->ensureIndex(index: $index);

        $sum     = 0;
        $current = $index + 1;

        while ( $current > 0 ) {
            $sum     += $this->tree[$current];
            $current -= $current & -$current;
        }

        return $sum;
    }

    #[Override]
    public function count() : int
    {
        return max(0, $this->size);
    }
}
