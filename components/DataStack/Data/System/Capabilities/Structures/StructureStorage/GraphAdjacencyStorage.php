<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Avax\Components\DataStack\Data\System\Foundation\Failure\DuplicateKey;
use Avax\Components\DataStack\Data\System\Foundation\Failure\MissingKey;
use Avax\Components\DataStack\Data\System\Foundation\Values\Edge;

/**
 * GraphAdjacencyStorage — immutable adjacency list storage for graph structures.
 *
 * Stores nodes and their neighbor lists. Supports both directed and undirected graphs.
 */
final readonly class GraphAdjacencyStorage
{
    /**
     * @var array<int|string, list<int|string>>
     */
    private array $adjacency;

    /**
     * @var list<int|string>
     */
    private array $nodes;

    /**
     * @param array<int|string, list<int|string>> $adjacency
     * @param list<int|string>                    $nodes
     */
    public function __construct(array $adjacency = [], array $nodes = [])
    {
        $this->adjacency = $adjacency;
        $this->nodes     = $nodes;
    }

    /**
     * Return a new storage with the given edge added.
     */
    public function addEdge(Edge $edge, bool $directed = true) : self
    {
        $storage = $this;

        if (! isset($storage->adjacency[$edge->from])) {
            $storage = $storage->addNode($edge->from);
        }
        if (! isset($storage->adjacency[$edge->to])) {
            $storage = $storage->addNode($edge->to);
        }

        $adjacency                = $storage->adjacency;
        $adjacency[$edge->from][] = $edge->to;

        if (! $directed) {
            $adjacency[$edge->to][] = $edge->from;
        }

        return new self(adjacency: $adjacency, nodes: $storage->nodes);
    }

    /**
     * Return a new storage with the given node added.
     */
    public function addNode(int|string $node) : self
    {
        if (isset($this->adjacency[$node])) {
            return $this;
        }

        return new self(
            adjacency: [...$this->adjacency, $node => []],
            nodes    : [...$this->nodes, $node],
        );
    }

    /**
     * Return a new storage with the given node removed.
     */
    public function removeNode(int|string $node) : self
    {
        if (! isset($this->adjacency[$node])) {
            return $this;
        }

        $adjacency = $this->adjacency;
        unset($adjacency[$node]);

        foreach ($adjacency as $n => $neighbors) {
            $adjacency[$n] = array_values(array_filter($neighbors, static fn ($neighbor) => $neighbor !== $node));
        }

        return new self(
            adjacency: $adjacency,
            nodes    : array_values(array_filter($this->nodes, static fn ($n) => $n !== $node)),
        );
    }

    /**
     * @return list<int|string>
     */
    public function neighbors(int|string $node) : array
    {
        return $this->adjacency[$node] ?? [];
    }

    public function hasNode(int|string $node) : bool
    {
        return isset($this->adjacency[$node]);
    }

    public function hasEdge(int|string $from, int|string $to) : bool
    {
        return in_array($to, $this->adjacency[$from] ?? [], strict: true);
    }

    /**
     * @return list<int|string>
     */
    public function nodes() : array
    {
        return $this->nodes;
    }

    public function nodeCount() : int
    {
        return count(value: $this->nodes);
    }

    public function edgeCount() : int
    {
        $count = 0;
        foreach ($this->adjacency as $neighbors) {
            $count += count(value: $neighbors);
        }

        return $count;
    }

    /**
     * @return array<int|string, list<int|string>>
     */
    public function toArray() : array
    {
        return $this->adjacency;
    }
}
