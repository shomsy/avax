<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

use InvalidArgumentException;

final readonly class ConsistentHashRing
{
    public function __construct(
        public int   $virtualNodes,
        public array $nodes,
        public array $ring
    )
    {
        if ($this->virtualNodes < 1) {
            throw new InvalidArgumentException(message: 'Virtual nodes must be at least 1.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'implements consistent hashing ring for distribution.';
    }

    public static function create(array $nodes, int $virtualNodes = 150) : self
    {
        $ring = [];

        foreach ($nodes as $node) {
            for ($i = 0; $i < $virtualNodes; $i++) {
                $key         = sprintf('%s-%d', $node, $i);
                $hash = $this->hash(key: $key);
                $ring[$hash] = $node;
            }
        }

        ksort($ring, SORT_NUMERIC);

        return new self(virtualNodes: $virtualNodes, nodes: $nodes, ring: $ring);
    }

    private function hash(string $key) : int
    {
        return crc32($key) & 0xFFFFFFFF;
    }

    public function getNode(string $key) : string
    {
        $hash = $this->hash(key: $key);

        foreach ($this->ring as $ringHash => $node) {
            if ($ringHash >= $hash) {
                return $node;
            }
        }

        return $this->nodes[0] ?? '';
    }

    public function addNode(string $node) : self
    {
        $newNodes = [...$this->nodes, $node];

        return self::create(nodes: $newNodes, virtualNodes: $this->virtualNodes);
    }

    public function removeNode(string $node) : self
    {
        $newNodes = array_filter($this->nodes, static fn ($n) => $n !== $node);

        return self::create(nodes: array_values($newNodes), virtualNodes: $this->virtualNodes);
    }

    public function toMetadata() : array
    {
        return [
            'virtual_nodes' => $this->virtualNodes,
            'node_count'    => count($this->nodes),
            'ring_size'     => count($this->ring),
        ];
    }
}