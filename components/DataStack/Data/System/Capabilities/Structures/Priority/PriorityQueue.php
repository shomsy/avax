<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority;

use Countable;
use Override;

final readonly class PriorityQueue implements Countable
{
    public function __construct(private BinaryHeap $heap) {}

    public static function min() : self
    {
        return new self(heap: BinaryHeap::min());
    }

    public static function max() : self
    {
        return new self(heap: BinaryHeap::max());
    }

    public function enqueue(mixed $value, int|float $priority) : self
    {
        return new self(heap: $this->heap->insert(value: $value, priority: $priority));
    }

    public function peek(mixed $default = null) : mixed
    {
        return $this->heap->peek(default: $default);
    }

    public function dequeue() : self
    {
        return new self(heap: $this->heap->removeRoot());
    }

    public function next() : mixed
    {
        return $this->heap->extract();
    }

    public function isEmpty() : bool
    {
        return $this->heap->isEmpty();
    }

    #[Override]
    public function count() : int
    {
        return $this->heap->count();
    }
}
