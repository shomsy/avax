<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Graphs;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\GraphStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\MissingKey;
use Countable;
use Override;

final readonly class Graph implements Countable, GraphStructure
{
    /**
     * @param array<array-key, array<array-key, bool>> $adjacency
     */
    public function __construct(private array $adjacency = [], private bool $directed = false) {}

    public static function directed() : self
    {
        return new self(directed: true);
    }

    public static function undirected() : self
    {
        return new self(directed: false);
    }

    public function addEdge(int|string $from, int|string $to) : self
    {
        $graph                 = $this->addNode(node: $from)->addNode(node: $to);
        $adjacency             = $graph->adjacency;
        $adjacency[$from][$to] = true;

        if (! $this->directed) {
            $adjacency[$to][$from] = true;
        }

        return new self(adjacency: $adjacency, directed: $this->directed);
    }

    public function addNode(int|string $node) : self
    {
        $adjacency        = $this->adjacency;
        $adjacency[$node] ??= [];

        return new self(adjacency: $adjacency, directed: $this->directed);
    }

    public function removeNode(int|string $node) : self
    {
        $adjacency = $this->adjacency;
        unset($adjacency[$node]);

        foreach ($adjacency as $source => $targets) {
            unset($targets[$node]);
            $adjacency[$source] = $targets;
        }

        return new self(adjacency: $adjacency, directed: $this->directed);
    }

    public function removeEdge(int|string $from, int|string $to) : self
    {
        $adjacency = $this->adjacency;
        unset($adjacency[$from][$to]);

        if (! $this->directed) {
            unset($adjacency[$to][$from]);
        }

        return new self(adjacency: $adjacency, directed: $this->directed);
    }

    #[Override]
    public function hasEdge(int|string $from, int|string $to) : bool
    {
        return isset($this->adjacency[$from][$to]);
    }

    /**
     * @return list<array-key>
     */
    #[Override]
    public function neighborsOf(int|string $node) : array
    {
        if (! $this->hasNode(node: $node)) {
            throw MissingKey::named(key: $node);
        }

        return array_keys(array: $this->adjacency[$node]);
    }

    #[Override]
    public function hasNode(int|string $node) : bool
    {
        return array_key_exists(key: $node, array: $this->adjacency);
    }

    /**
     * @return array<array-key, list<array-key>>
     */
    public function toArray() : array
    {
        $result = [];

        foreach ($this->adjacency as $node => $neighbors) {
            $result[$node] = array_keys(array: $neighbors);
        }

        return $result;
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->adjacency === [];
    }

    #[Override]
    public function count() : int
    {
        return count(value: $this->adjacency);
    }
}
