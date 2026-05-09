<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Graphs\Graph;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Graphs\UnionFind;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\Deque;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\Queue;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\RingBuffer;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\SparseArray;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Linear\Stack;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Matrix\DenseMatrix;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Matrix\SparseMatrix;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority\BinaryHeap;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Priority\PriorityQueue;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Probabilistic\BloomFilter;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees\BinarySearchTree;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees\FenwickTree;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees\SegmentTree;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees\Trie;
use Avax\Components\DataStack\Data\System\Foundation\Failure\EmptyStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Avax\Components\DataStack\Data\System\PublicSurface\BloomFilter as BloomFilterSurface;
use Avax\Components\DataStack\Data\System\PublicSurface\Deque as DequeSurface;
use Avax\Components\DataStack\Data\System\PublicSurface\Graph as GraphSurface;
use Avax\Components\DataStack\Data\System\PublicSurface\Heap as HeapSurface;
use Avax\Components\DataStack\Data\System\PublicSurface\Matrix as MatrixSurface;
use Avax\Components\DataStack\Data\System\PublicSurface\PriorityQueue as PriorityQueueSurface;
use Avax\Components\DataStack\Data\System\PublicSurface\Queue as QueueSurface;
use Avax\Components\DataStack\Data\System\PublicSurface\Stack as StackSurface;
use PHPUnit\Framework\TestCase;

final class CoreStructuresTest extends TestCase
{
    public function test_stack_pops_last_pushed_value_without_mutating_original() : void
    {
        $stack = (new Stack())->push(value: 'A')->push(value: 'B');

        self::assertSame('B', $stack->peek());
        self::assertSame(['A'], $stack->pop()->toArray());
        self::assertSame(['A', 'B'], $stack->toArray());
    }

    public function test_stack_rejects_pop_from_empty_structure() : void
    {
        $this->expectException(EmptyStructure::class);

        (new Stack())->pop();
    }

    public function test_queue_dequeues_first_enqueued_value() : void
    {
        $queue = (new Queue())->enqueue(value: 'A')->enqueue(value: 'B');

        self::assertSame('A', $queue->front());
        self::assertSame(['B'], $queue->dequeue()->toArray());
        self::assertSame(['A', 'B'], $queue->toArray());
    }

    public function test_deque_operates_on_both_ends() : void
    {
        $deque = (new Deque())->pushBack(value: 'B')->pushFront(value: 'A')->pushBack(value: 'C');

        self::assertSame(['A', 'B', 'C'], $deque->toArray());
        self::assertSame(['B', 'C'], $deque->popFront()->toArray());
        self::assertSame(['A', 'B'], $deque->popBack()->toArray());
    }

    public function test_ring_buffer_enforces_capacity() : void
    {
        $buffer = RingBuffer::empty(capacity: 2)->enqueue(value: 'A')->enqueue(value: 'B');

        self::assertTrue($buffer->isFull());
        self::assertSame('A', $buffer->front());
        self::assertSame(['B'], $buffer->dequeue()->toArray());
    }

    public function test_sparse_array_stores_only_non_null_values() : void
    {
        $array = (new SparseArray(items: [0 => 'A', 4 => null]))->put(index: 10, value: 'B');

        self::assertSame([0, 10], $array->keys());
        self::assertSame('B', $array->get(key: 10));
        self::assertSame([0 => 'A'], $array->put(index: 10, value: null)->toArray());
    }

    public function test_binary_heap_extracts_minimum_priority_order() : void
    {
        $heap = BinaryHeap::min()
            ->insert(value: 'slow', priority: 10)
            ->insert(value: 'fast', priority: 1)
            ->insert(value: 'middle', priority: 5);

        self::assertSame('fast', $heap->peek());
        self::assertSame(['fast', 'middle', 'slow'], $heap->valuesInPriorityOrder());
    }

    public function test_priority_queue_extracts_maximum_priority_order() : void
    {
        $queue = PriorityQueue::max()
            ->enqueue(value: 'low', priority: 1)
            ->enqueue(value: 'high', priority: 9);

        self::assertSame('high', $queue->next());
        self::assertSame('low', $queue->dequeue()->next());
    }

    public function test_binary_search_tree_keeps_sorted_traversal_and_unique_values() : void
    {
        $tree = BinarySearchTree::empty()
            ->insert(value: 5)
            ->insert(value: 2)
            ->insert(value: 7)
            ->insert(value: 5);

        self::assertTrue($tree->contains(value: 2));
        self::assertFalse($tree->contains(value: 9));
        self::assertSame([2, 5, 7], $tree->toArray());
        self::assertSame(3, $tree->count());
    }

    public function test_fenwick_tree_answers_prefix_and_range_sums() : void
    {
        $tree = FenwickTree::withSize(size: 5)
            ->add(index: 0, delta: 1)
            ->add(index: 1, delta: 2)
            ->add(index: 4, delta: 5);

        self::assertSame(3, $tree->prefixSum(index: 1));
        self::assertSame(7, $tree->rangeSum(from: 1, to: 4));
    }

    public function test_fenwick_tree_rejects_invalid_index() : void
    {
        $this->expectException(IndexOutOfBounds::class);

        FenwickTree::withSize(size: 2)->add(index: 3, delta: 1);
    }

    public function test_segment_tree_answers_range_sums() : void
    {
        $tree = new SegmentTree(values: [1, 2, 3, 4, 5]);

        self::assertSame(9, $tree->rangeSum(from: 1, to: 3));
        self::assertSame(15, $tree->rangeSum(from: 0, to: 4));
    }

    public function test_trie_finds_words_and_prefixes() : void
    {
        $trie = Trie::empty()->insert(word: 'avax')->insert(word: 'avatar');

        self::assertTrue($trie->contains(word: 'avax'));
        self::assertFalse($trie->contains(word: 'ava'));
        self::assertTrue($trie->hasPrefix(prefix: 'ava'));
        self::assertSame(2, $trie->count());
    }

    public function test_graph_keeps_node_edge_consistency() : void
    {
        $graph = Graph::undirected()->addEdge(from: 'A', to: 'B')->addEdge(from: 'B', to: 'C');

        self::assertTrue($graph->hasNode(node: 'A'));
        self::assertTrue($graph->hasEdge(from: 'A', to: 'B'));
        self::assertSame(['A', 'C'], $graph->neighborsOf(node: 'B'));
        self::assertFalse($graph->removeNode(node: 'B')->hasEdge(from: 'A', to: 'B'));
    }

    public function test_union_find_connected_relation_is_transitive() : void
    {
        $set = new UnionFind(items: ['A', 'B', 'C']);
        $set->union(left: 'A', right: 'B');
        $set->union(left: 'B', right: 'C');

        self::assertTrue($set->connected(left: 'A', right: 'C'));
        self::assertSame(3, $set->count());
    }

    public function test_dense_matrix_preserves_dimensions_and_cells() : void
    {
        $matrix = DenseMatrix::filled(rows: 2, columns: 3, value: 0)->put(row: 1, column: 2, value: 9);

        self::assertSame(2, $matrix->rows());
        self::assertSame(3, $matrix->columns());
        self::assertSame(9, $matrix->get(row: 1, column: 2));
        self::assertSame(6, $matrix->count());
    }

    public function test_dense_matrix_rejects_jagged_rows() : void
    {
        $this->expectException(InvalidCapacity::class);

        new DenseMatrix(rows: [[1], [1, 2]]);
    }

    public function test_sparse_matrix_stores_only_present_cells() : void
    {
        $matrix = (new SparseMatrix(rows: 3, columns: 3))
            ->put(row: 2, column: 1, value: 'X')
            ->put(row: 0, column: 0, value: null);

        self::assertSame('X', $matrix->get(row: 2, column: 1));
        self::assertSame('missing', $matrix->get(row: 0, column: 0, default: 'missing'));
        self::assertSame(['2:1' => 'X'], $matrix->storedCells());
    }

    public function test_bloom_filter_never_reports_inserted_value_absent() : void
    {
        $filter = BloomFilter::empty(bits: 64, hashCount: 3)->add(value: 'avax');

        self::assertTrue($filter->mightContain(value: 'avax'));
        self::assertSame(1, $filter->count());
    }

    public function test_public_surface_exposes_stable_core_structures() : void
    {
        self::assertInstanceOf(Stack::class, StackSurface::make(values: ['A']));
        self::assertInstanceOf(Queue::class, QueueSurface::make(values: ['A']));
        self::assertInstanceOf(Deque::class, DequeSurface::make(values: ['A']));
        self::assertInstanceOf(BinaryHeap::class, HeapSurface::min());
        self::assertInstanceOf(PriorityQueue::class, PriorityQueueSurface::max());
        self::assertInstanceOf(Graph::class, GraphSurface::directed());
        self::assertInstanceOf(DenseMatrix::class, MatrixSurface::filled(rows: 1, columns: 1));
        self::assertInstanceOf(SparseMatrix::class, MatrixSurface::sparse(rows: 1, columns: 1));
        self::assertInstanceOf(BloomFilter::class, BloomFilterSurface::empty());
    }
}
