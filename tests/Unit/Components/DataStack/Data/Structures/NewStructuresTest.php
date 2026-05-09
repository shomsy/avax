<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data\Structures;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\DoublyLinkedList;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\DynamicArray;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\LinkedList;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority\MaxHeap;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority\MinHeap;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Probabilistic\CountMinSketch;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Probabilistic\HyperLogLog;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Graphs\WeightedGraph;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Sets\Bag;
use Avax\Components\DataStack\Data\System\Foundation\Failure\EmptyStructure;
use PHPUnit\Framework\TestCase;

final class NewStructuresTest extends TestCase
{
    // ========== Bag Tests ==========

    public function test_bag_preserves_duplicate_counts() : void
    {
        $bag = Bag::from(items: ['A', 'A', 'B']);

        self::assertSame(3, $bag->count());
        self::assertSame(2, $bag->countOf(value: 'A'));
        self::assertSame(1, $bag->countOf(value: 'B'));
        self::assertTrue($bag->contains(value: 'A'));
        self::assertTrue($bag->contains(value: 'B'));
        self::assertFalse($bag->contains(value: 'C'));
    }

    public function test_bag_add_and_remove() : void
    {
        $bag = Bag::from(items: ['A', 'A'])->add(value: 'A');

        self::assertSame(3, $bag->count());
        self::assertSame(3, $bag->countOf(value: 'A'));

        $bag2 = $bag->remove(value: 'A');
        self::assertSame(2, $bag2->count());
        self::assertSame(2, $bag2->countOf(value: 'A'));

        $bag3 = $bag2->remove(value: 'A')->remove(value: 'A');
        self::assertTrue($bag3->isEmpty());
        self::assertSame(0, $bag3->countOf(value: 'A'));
    }

    public function test_bag_remove_unknown_is_noop() : void
    {
        $bag = Bag::from(items: ['A']);
        $bag2 = $bag->remove(value: 'B');

        self::assertSame($bag->count(), $bag2->count());
    }

    public function test_bag_frequencies() : void
    {
        $bag = Bag::from(items: ['X', 'X', 'X', 'Y', 'Y']);
        $freq = $bag->frequencies();

        self::assertGreaterThan(0, count(value: $freq));
    }

    public function test_bag_union() : void
    {
        $bag = Bag::from(items: ['A'])->union(items: ['A', 'B']);

        self::assertSame(3, $bag->count());
        self::assertSame(2, $bag->countOf(value: 'A'));
    }

    public function test_bag_to_array() : void
    {
        $bag = Bag::from(items: ['A', 'B', 'A']);
        $arr = $bag->toArray();

        self::assertSame(3, count(value: $arr));
        self::assertContains('A', $arr);
        self::assertContains('B', $arr);
    }

    // ========== MinHeap Tests ==========

    public function test_min_heap_extracts_smallest_first() : void
    {
        $heap = MinHeap::empty()
            ->insert(value: 'slow', priority: 10)
            ->insert(value: 'fast', priority: 1)
            ->insert(value: 'middle', priority: 5);

        self::assertSame('fast', $heap->peek());
        self::assertSame(['fast', 'middle', 'slow'], $heap->valuesInPriorityOrder());
    }

    public function test_min_heap_empty_operations() : void
    {
        $heap = MinHeap::empty();

        self::assertTrue($heap->isEmpty());
        self::assertNull($heap->peek());
        self::assertSame(0, $heap->count());
    }

    public function test_min_heap_extract_throws_when_empty() : void
    {
        $this->expectException(EmptyStructure::class);
        MinHeap::empty()->extract();
    }

    // ========== MaxHeap Tests ==========

    public function test_max_heap_extracts_largest_first() : void
    {
        $heap = MaxHeap::empty()
            ->insert(value: 'low', priority: 1)
            ->insert(value: 'high', priority: 9)
            ->insert(value: 'middle', priority: 5);

        self::assertSame('high', $heap->peek());
        self::assertSame(['high', 'middle', 'low'], $heap->valuesInPriorityOrder());
    }

    public function test_max_heap_from_iterable() : void
    {
        $heap = MaxHeap::from(items: [
            ['value' => 'A', 'priority' => 3],
            ['value' => 'B', 'priority' => 7],
        ]);

        self::assertSame('B', $heap->peek());
    }

    // ========== LinkedList Tests ==========

    public function test_linked_list_prepend_and_head() : void
    {
        $list = LinkedList::empty()->prepend(value: 'B')->prepend(value: 'A');

        self::assertSame('A', $list->head());
        self::assertSame('A', $list->first());
        self::assertSame('B', $list->last());
    }

    public function test_linked_list_append() : void
    {
        $list = LinkedList::empty()->append(value: 'A')->append(value: 'B');

        self::assertSame('A', $list->head());
        self::assertSame('B', $list->last());
    }

    public function test_linked_list_tail() : void
    {
        $list = LinkedList::from(items: ['A', 'B', 'C']);

        self::assertSame(['B', 'C'], $list->tail()->toArray());
    }

    public function test_linked_list_reverse() : void
    {
        $list = LinkedList::from(items: ['A', 'B', 'C']);

        self::assertSame(['C', 'B', 'A'], $list->reverse()->toArray());
    }

    public function test_linked_list_tail_of_empty_throws() : void
    {
        $this->expectException(EmptyStructure::class);
        LinkedList::empty()->tail();
    }

    // ========== DoublyLinkedList Tests ==========

    public function test_doubly_linked_list_prepend_and_append() : void
    {
        $list = DoublyLinkedList::empty()->prepend(value: 'B')->prepend(value: 'A')->append(value: 'C');

        self::assertSame('A', $list->first());
        self::assertSame('C', $list->last());
        self::assertSame(['A', 'B', 'C'], $list->toArray());
    }

    public function test_doubly_linked_list_reverse() : void
    {
        $list = DoublyLinkedList::from(items: ['A', 'B', 'C']);

        self::assertSame(['C', 'B', 'A'], $list->reverse()->toArray());
        self::assertSame(['C', 'B', 'A'], $list->reverseToArray());
    }

    public function test_doubly_linked_list_init_and_tail() : void
    {
        $list = DoublyLinkedList::from(items: ['A', 'B', 'C']);

        self::assertSame(['A', 'B'], $list->init()->toArray());
        self::assertSame(['B', 'C'], $list->tail()->toArray());
    }

    // ========== DynamicArray Tests ==========

    public function test_dynamic_array_append_grows() : void
    {
        $arr = DynamicArray::empty(initialCapacity: 2)
            ->append(value: 'A')
            ->append(value: 'B')
            ->append(value: 'C');

        self::assertSame(['A', 'B', 'C'], $arr->toArray());
        self::assertSame(3, $arr->count());
        self::assertGreaterThan(2, $arr->getCapacity());
    }

    public function test_dynamic_array_put_and_get() : void
    {
        $arr = DynamicArray::from(items: ['A', 'B'])->put(index: 1, value: 'X');

        self::assertSame('X', $arr->get(index: 1));
    }

    public function test_dynamic_array_put_expands() : void
    {
        $arr = DynamicArray::from(items: ['A'])->put(index: 3, value: 'D');

        self::assertSame('D', $arr->get(index: 3));
        self::assertNull($arr->get(index: 2));
    }

    public function test_dynamic_array_remove_at() : void
    {
        $arr = DynamicArray::from(items: ['A', 'B', 'C'])->removeAt(index: 1);

        self::assertSame(['A', 'C'], $arr->toArray());
    }

    // ========== CountMinSketch Tests ==========

    public function test_count_min_sketch_never_undercounts() : void
    {
        $sketch = CountMinSketch::empty(depth: 5, width: 1000);
        $sketch = $sketch->add(value: 'A')->add(value: 'A')->add(value: 'A');

        self::assertGreaterThanOrEqual(3, $sketch->estimate(value: 'A'));
    }

    public function test_count_min_sketch_might_contain() : void
    {
        $sketch = CountMinSketch::empty()->add(value: 'X');

        self::assertTrue($sketch->mightContain(value: 'X'));
        self::assertFalse($sketch->mightContain(value: 'never_added'));
    }

    public function test_count_min_sketch_empty() : void
    {
        $sketch = CountMinSketch::empty();

        self::assertTrue($sketch->isEmpty());
        self::assertSame(0, $sketch->count());
        self::assertSame(0, $sketch->estimate(value: 'anything'));
    }

    // ========== HyperLogLog Tests ==========

    public function test_hyper_log_log_estimates_cardinality() : void
    {
        $hll = HyperLogLog::empty(precision: 14);
        for ($i = 0; $i < 1000; $i++) {
            $hll = $hll->add(value: "item:{$i}");
        }

        $estimate = $hll->count();
        $error = abs($estimate - 1000) / 1000;

        self::assertLessThan(0.05, $error, 'HLL estimate should be within 5% for 1000 items');
    }

    public function test_hyper_log_log_does_not_support_membership() : void
    {
        $hll = HyperLogLog::empty()->add(value: 'X');

        self::assertFalse($hll->mightContain(value: 'X'));
    }

    public function test_hyper_log_log_empty() : void
    {
        $hll = HyperLogLog::empty();

        self::assertTrue($hll->isEmpty());
        self::assertSame(0, $hll->count());
    }

    // ========== WeightedGraph Tests ==========

    public function test_weighted_graph_directed_edge_and_weight() : void
    {
        $graph = (new WeightedGraph(directed: true))->addEdge(from: 'A', to: 'B', weight: 5);

        self::assertTrue($graph->hasEdge(from: 'A', to: 'B'));
        self::assertFalse($graph->hasEdge(from: 'B', to: 'A'));
        self::assertSame(5, $graph->weightOf(from: 'A', to: 'B'));
        self::assertSame(2, $graph->count());
    }

    public function test_weighted_graph_undirected_reciprocal() : void
    {
        $graph = (new WeightedGraph(directed: false))->addEdge(from: 'A', to: 'B', weight: 3);

        self::assertTrue($graph->hasEdge(from: 'A', to: 'B'));
        self::assertTrue($graph->hasEdge(from: 'B', to: 'A'));
        self::assertSame(3, $graph->weightOf(from: 'A', to: 'B'));
        self::assertSame(3, $graph->weightOf(from: 'B', to: 'A'));
    }

    public function test_weighted_graph_has_node_and_nodes() : void
    {
        $graph = (new WeightedGraph())
            ->addEdge(from: 'A', to: 'B', weight: 1)
            ->addEdge(from: 'B', to: 'C', weight: 2);

        self::assertTrue($graph->hasNode(node: 'A'));
        self::assertTrue($graph->hasNode(node: 'B'));
        self::assertTrue($graph->hasNode(node: 'C'));
        self::assertFalse($graph->hasNode(node: 'D'));
        self::assertSame(3, count(value: $graph->nodes()));
    }

    public function test_weighted_graph_neighbors() : void
    {
        $graph = (new WeightedGraph())
            ->addEdge(from: 'A', to: 'B', weight: 1)
            ->addEdge(from: 'A', to: 'C', weight: 2);

        $neighbors = $graph->neighborsOf(node: 'A');
        self::assertCount(2, $neighbors);
        self::assertContains('B', $neighbors);
        self::assertContains('C', $neighbors);
    }

    public function test_weighted_graph_immutability() : void
    {
        $original = new WeightedGraph();
        $modified = $original->addEdge(from: 'A', to: 'B', weight: 1);

        self::assertTrue($original->isEmpty());
        self::assertFalse($modified->isEmpty());
        self::assertFalse($original->hasEdge(from: 'A', to: 'B'));
        self::assertTrue($modified->hasEdge(from: 'A', to: 'B'));
    }

    public function test_weighted_graph_empty() : void
    {
        $graph = new WeightedGraph();

        self::assertTrue($graph->isEmpty());
        self::assertSame(0, $graph->count());
        self::assertSame([], $graph->nodes());
    }

    public function test_weighted_graph_default_weight() : void
    {
        $graph = new WeightedGraph();

        self::assertNull($graph->weightOf(from: 'X', to: 'Y'));
        self::assertSame(0, $graph->weightOf(from: 'X', to: 'Y', default: 0));
    }
}
