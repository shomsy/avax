<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\DistributeCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final class ConsistentHashRing
{
    private const VIRTUAL_NODES     = 150;
    private const MIN_RING_POSITION = 0;
    private const MAX_RING_POSITION = PHP_INT_MAX;

    /** @var array<int, CacheNode> */
    private array $ring = [];
    /** @var array<CacheNodeId, array<int>> */
    private array $nodePositions = [];
    /** @var array<CacheNodeId, CacheNode> */
    private array $nodes = [];

    public function __construct(
        private int $virtualNodes = self::VIRTUAL_NODES
    ) {}

    public function addNode(CacheNode $node) : self
    {
        $this->nodes[$node->id->toString()]         = $node;
        $this->nodePositions[$node->id->toString()] = [];

        for ($i = 0; $i < $this->virtualNodes; $i++) {
            $position                                     = $this->hash(sprintf('%s:%d', $node->id->toString(), $i));
            $this->ring[$position]                        = $node;
            $this->nodePositions[$node->id->toString()][] = $position;
        }

        $this->sortRing();

        return $this;
    }

    private function hash(string $value) : int
    {
        return abs(crc32($value));
    }

    private function sortRing() : void
    {
        ksort($this->ring, SORT_NUMERIC);
    }

    public function removeNode(CacheNodeId $nodeId) : self
    {
        $nodeIdStr = $nodeId->toString();

        if (! isset($this->nodes[$nodeIdStr])) {
            return $this;
        }

        unset($this->nodes[$nodeIdStr]);

        foreach ($this->nodePositions[$nodeIdStr] ?? [] as $position) {
            unset($this->ring[$position]);
        }

        unset($this->nodePositions[$nodeIdStr]);

        return $this;
    }

    public function getNodeForPartition(int $partitionIndex) : ?CacheNode
    {
        $partitionHash = $this->hash((string) $partitionIndex);

        return $this->getNodeForKey(
            CacheKey::create((string) $partitionHash)
        );
    }

    public function getNodeForKey(CacheKey $key) : ?CacheNode
    {
        if (count($this->ring) === 0) {
            return null;
        }

        $keyHash = $this->hash($key->fullKey());

        $closestNode     = null;
        $closestPosition = null;

        foreach ($this->ring as $position => $node) {
            if ($position >= $keyHash) {
                if ($closestNode === null || $position < $closestPosition) {
                    $closestNode     = $node;
                    $closestPosition = $position;
                }
            }
        }

        if ($closestNode === null) {
            $positions     = array_keys($this->ring);
            $firstPosition = reset($positions);

            return $this->ring[$firstPosition] ?? null;
        }

        return $closestNode;
    }

    public function nodeCount() : int
    {
        return count($this->nodes);
    }

    public function isEmpty() : bool
    {
        return count($this->nodes) === 0;
    }
}