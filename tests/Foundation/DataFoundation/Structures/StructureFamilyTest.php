<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataFoundation\Structures;

use Avax\Components\DataStack\Database\Structures\Deque\Deque;
use Avax\Components\DataStack\Database\Structures\PriorityQueue\PriorityQueue;
use Avax\Components\DataStack\Database\Structures\Queue\Queue;
use Avax\Components\DataStack\Database\Structures\RingBuffer\RingBuffer;
use Avax\Components\DataStack\Database\Structures\Stack\Stack;
use Avax\Components\DataStack\Database\Structures\Tree\Tree;
use Avax\Tests\TestCase;

final class StructureFamilyTest extends TestCase
{
    public function testStackPopsLastValue() : void
    {
        $result = new Stack(items: [1, 2, 3])->pop();

        $this->assertSame(3, $result->first());
        $this->assertSame([1, 2], $result->second()->all());
    }

    public function testQueueDequeuesFirstValue() : void
    {
        $result = new Queue(items: [1, 2, 3])->dequeue();

        $this->assertSame(1, $result->first());
        $this->assertSame([2, 3], $result->second()->all());
    }

    public function testDequeWorksOnBothEnds() : void
    {
        $deque = new Deque(items: [2])->pushFront(value: 1)->pushBack(value: 3);

        $this->assertSame([1, 2, 3], $deque->all());
    }

    public function testPriorityQueueReturnsHighestPriorityFirst() : void
    {
        $result = new PriorityQueue()
            ->push(value: 'low', priority: 1)
            ->push(value: 'high', priority: 10)
            ->pull();

        $this->assertSame('high', $result->first());
    }

    public function testRingBufferDropsOldestValueWhenFull() : void
    {
        $buffer = new RingBuffer(capacity: 2, items: [1, 2])->append(value: 3);

        $this->assertSame([2, 3], $buffer->all());
    }

    public function testTreeAddsChildrenImmutably() : void
    {
        $tree = new Tree(value: 'root')->addChild(child: new Tree(value: 'leaf'));

        $this->assertCount(1, $tree->children());
        $this->assertSame('leaf', $tree->children()[0]->value());
    }
}
