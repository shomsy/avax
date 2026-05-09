<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Data\StoragePrimitives;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\BinaryNodeStorage;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\GraphAdjacencyStorage;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\LinkedNodeStorage;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\MatrixDenseStorage;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\MatrixSparseStorage;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\TreeNodeStorage;
use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidStructureOperation;
use Avax\Components\DataStack\Data\System\Foundation\Values\Edge;
use PHPUnit\Framework\TestCase;

final class StoragePrimitivesTest extends TestCase
{
    // -- LinkedNodeStorage --

    public function test_linked_node_storage_prepend_and_append() : void
    {
        $node      = new LinkedNodeStorage(value: 'a');
        $prepended = $node->prepend(value: 'b');
        self::assertSame('b', $prepended->value);
        self::assertNotNull($prepended->next);
        self::assertSame('a', $prepended->next->value);
        self::assertSame(2, $prepended->length());

        $appended = $node->append(value: 'c');
        self::assertSame('a', $appended->value);
        self::assertNotNull($appended->next);
        self::assertSame('c', $appended->next->value);
        self::assertSame(2, $appended->length());
    }

    public function test_linked_node_storage_to_array() : void
    {
        $node = new LinkedNodeStorage(value: 'a', next: new LinkedNodeStorage(value: 'b'));
        self::assertSame(['a', 'b'], $node->toArray());
    }

    // -- BinaryNodeStorage --

    public function test_binary_node_storage_with_children() : void
    {
        $left  = new BinaryNodeStorage(value: 'left');
        $right = new BinaryNodeStorage(value: 'right');
        $root  = new BinaryNodeStorage(value: 'root')->withLeft($left)->withRight($right);

        self::assertSame('root', $root->value);
        self::assertNotNull($root->left);
        self::assertSame('left', $root->left->value);
        self::assertNotNull($root->right);
        self::assertSame('right', $root->right->value);
    }

    public function test_binary_node_storage_count() : void
    {
        $node = new BinaryNodeStorage(
            value: 'root',
            left : new BinaryNodeStorage(value: 'left'),
            right: new BinaryNodeStorage(value: 'right'),
        );
        self::assertSame(3, $node->count());
    }

    public function test_binary_node_storage_traversals() : void
    {
        $node = new BinaryNodeStorage(
            value: 'root',
            left : new BinaryNodeStorage(value: 'left'),
            right: new BinaryNodeStorage(value: 'right'),
        );
        self::assertSame(['left', 'root', 'right'], $node->inOrder());
        self::assertSame(['root', 'left', 'right'], $node->preOrder());
        self::assertSame(['left', 'right', 'root'], $node->postOrder());
    }

    // -- TreeNodeStorage --

    public function test_tree_node_storage_add_child() : void
    {
        $root    = new TreeNodeStorage(value: 'root');
        $child   = new TreeNodeStorage(value: 'child');
        $updated = $root->addChild($child);

        self::assertEmpty($root->children);
        self::assertSame(1, count(value: $updated->children));
        self::assertSame('child', $updated->children[0]->value);
    }

    public function test_tree_node_storage_count_and_depth() : void
    {
        $leaf1 = new TreeNodeStorage(value: 'leaf1');
        $leaf2 = new TreeNodeStorage(value: 'leaf2');
        $child = new TreeNodeStorage(value: 'child', children: [$leaf1, $leaf2]);
        $root  = new TreeNodeStorage(value: 'root', children: [$child]);

        self::assertSame(4, $root->count());
        self::assertSame(3, $root->depth());
    }

    public function test_tree_node_storage_traversals() : void
    {
        $leaf1 = new TreeNodeStorage(value: 'leaf1');
        $leaf2 = new TreeNodeStorage(value: 'leaf2');
        $child = new TreeNodeStorage(value: 'child', children: [$leaf1, $leaf2]);
        $root  = new TreeNodeStorage(value: 'root', children: [$child]);

        self::assertSame(['root', 'child', 'leaf1', 'leaf2'], $root->preOrder());
        self::assertSame(['leaf1', 'leaf2', 'child', 'root'], $root->postOrder());
        self::assertSame(['root', 'child', 'leaf1', 'leaf2'], $root->levelOrder());
    }

    public function test_tree_node_storage_contains() : void
    {
        $node = new TreeNodeStorage(value: 'root', children: [
            new TreeNodeStorage(value: 'child', children: [
                new TreeNodeStorage(value: 'leaf'),
            ]),
        ]);

        self::assertTrue($node->contains(value: 'leaf'));
        self::assertFalse($node->contains(value: 'missing'));
    }

    // -- GraphAdjacencyStorage --

    public function test_graph_adjacency_add_node() : void
    {
        $storage = new GraphAdjacencyStorage();
        $updated = $storage->addNode(node: 'A')->addNode(node: 'B');

        self::assertTrue($updated->hasNode('A'));
        self::assertTrue($updated->hasNode('B'));
        self::assertEmpty($updated->neighbors('A'));
    }

    public function test_graph_adjacency_add_edge() : void
    {
        $storage = new GraphAdjacencyStorage();
        $edge    = new Edge(from: 'A', to: 'B');
        $updated = $storage->addEdge(edge: $edge);

        self::assertTrue($updated->hasEdge('A', 'B'));
        self::assertFalse($updated->hasEdge('B', 'A'));
        self::assertSame(2, $updated->nodeCount());
        self::assertSame(1, $updated->edgeCount());
    }

    public function test_graph_adjacency_undirected_edge() : void
    {
        $storage = new GraphAdjacencyStorage();
        $edge    = new Edge(from: 'A', to: 'B');
        $updated = $storage->addEdge(edge: $edge, directed: false);

        self::assertTrue($updated->hasEdge('A', 'B'));
        self::assertTrue($updated->hasEdge('B', 'A'));
    }

    public function test_graph_adjacency_remove_node() : void
    {
        $storage = new GraphAdjacencyStorage();
        $edge    = new Edge(from: 'A', to: 'B');
        $updated = $storage->addEdge(edge: $edge)->removeNode(node: 'A');

        self::assertFalse($updated->hasNode('A'));
        self::assertFalse($updated->hasEdge('B', 'A'));
    }

    public function test_graph_adjacency_to_array() : void
    {
        $storage = new GraphAdjacencyStorage();
        $updated = $storage->addNode('A')->addNode('B');
        self::assertSame(['A' => [], 'B' => []], $updated->toArray());
    }

    // -- MatrixDenseStorage --

    public function test_matrix_dense_fill_and_get() : void
    {
        $matrix = MatrixDenseStorage::fill(rows: 3, columns: 3, value: 0);
        self::assertSame(3, $matrix->rowCount());
        self::assertSame(3, $matrix->columnCount());
        self::assertSame(0, $matrix->get(row: 0, column: 0));
    }

    public function test_matrix_dense_set_returns_new_instance() : void
    {
        $matrix  = MatrixDenseStorage::fill(rows: 2, columns: 2);
        $updated = $matrix->set(row: 0, column: 0, value: 99);

        self::assertSame(0, $matrix->get(row: 0, column: 0));
        self::assertSame(99, $updated->get(row: 0, column: 0));
    }

    public function test_matrix_dense_rejects_invalid_dimensions() : void
    {
        $this->expectException(InvalidStructureOperation::class);
        new MatrixDenseStorage(rows: []);
    }

    public function test_matrix_dense_rejects_uneven_rows() : void
    {
        $this->expectException(InvalidStructureOperation::class);
        new MatrixDenseStorage(rows: [[1, 2], [3]]);
    }

    public function test_matrix_dense_rejects_out_of_bounds() : void
    {
        $matrix = MatrixDenseStorage::fill(rows: 2, columns: 2);
        $this->expectException(IndexOutOfBounds::class);
        $matrix->get(row: 5, column: 0);
    }

    public function test_matrix_dense_flat() : void
    {
        $matrix = new MatrixDenseStorage(rows: [[1, 2], [3, 4]]);
        self::assertSame([1, 2, 3, 4], $matrix->flat());
    }

    // -- MatrixSparseStorage --

    public function test_matrix_sparse_default_value() : void
    {
        $matrix = MatrixSparseStorage::empty(rows: 10, columns: 10, default: 0);
        self::assertSame(0, $matrix->get(row: 5, column: 5));
        self::assertSame(0, $matrix->nonZeroCount());
        self::assertSame(1.0, $matrix->sparsity());
    }

    public function test_matrix_sparse_set_and_get() : void
    {
        $matrix  = MatrixSparseStorage::empty(rows: 5, columns: 5);
        $updated = $matrix->set(row: 1, column: 2, value: 42);

        self::assertSame(42, $updated->get(row: 1, column: 2));
        self::assertSame(0, $matrix->get(row: 1, column: 2));
        self::assertSame(1, $updated->nonZeroCount());
    }

    public function test_matrix_sparse_removes_cell_when_default() : void
    {
        $matrix  = MatrixSparseStorage::empty(rows: 5, columns: 5);
        $updated = $matrix->set(row: 0, column: 0, value: 5)->set(row: 0, column: 0, value: 0);
        self::assertSame(0, $updated->nonZeroCount());
    }

    public function test_matrix_sparse_to_dense() : void
    {
        $matrix  = MatrixSparseStorage::empty(rows: 2, columns: 2);
        $updated = $matrix->set(row: 0, column: 1, value: 99);
        $dense   = $updated->toDense();
        self::assertSame([[0, 99], [0, 0]], $dense);
    }

    public function test_matrix_sparse_rejects_invalid_dimensions() : void
    {
        $this->expectException(InvalidStructureOperation::class);
        MatrixSparseStorage::empty(rows: 0, columns: 5);
    }
}
