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
    public function test_stack_pops_last_value() : void
    {
        $result = new Stack(items: [1, 2, 3])->pop();

        $this->assertSame(3, $result->first());
        $this->assertSame([1, 2], $result->second()->all());
    }

    public function test_queue_dequeues_first_value() : void
    {
        $result = new Queue(items: [1, 2, 3])->dequeue();

        $this->assertSame(1, $result->first());
        $this->assertSame([2, 3], $result->second()->all());
    }

    public function test_deque_works_on_both_ends() : void
    {
        $deque = new Deque(items: [2])->pushFront(value: 1)->pushBack(value: 3);

        $this->assertSame([1, 2, 3], $deque->all());
    }

    public function test_priority_queue_returns_highest_priority_first() : void
    {
        $result = new PriorityQueue()
            ->push(value: 'low', priority: 1)
            ->push(value: 'high', priority: 10)
            ->pull();

        $this->assertSame('high', $result->first());
    }

    public function test_ring_buffer_drops_oldest_value_when_full() : void
    {
        $buffer = new RingBuffer(capacity: 2, items: [1, 2])->append(value: 3);

        $this->assertSame([2, 3], $buffer->all());
    }

    public function test_tree_adds_children_immutably() : void
    {
        $tree = new Tree(value: 'root')->addChild(child: new Tree(value: 'leaf'));

        $this->assertCount(1, $tree->children());
        $this->assertSame('leaf', $tree->children()[0]->value());
    }
}
