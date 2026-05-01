<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final class ConsistentHashRing
{
    private const int VIRTUAL_NODES = 150;



    /** @var array<int, CacheNode> */
    private array $ring = [];

    /** @var array<CacheNodeId, array<int>> */
    private array $nodePositions = [];

    /** @var array<CacheNodeId, CacheNode> */
    private array $nodes = [];

    public function __construct(
        private readonly int $virtualNodes = self::VIRTUAL_NODES,
    ) {}

    public function addNode(CacheNode $node) : self
    {
        $this->nodes[$node->id->toString()]         = $node;
        $this->nodePositions[$node->id->toString()] = [];

        for ($i = 0; $i < $this->virtualNodes; $i++) {
            $position                                     = $this->hash(value: sprintf('%s:%d', $node->id->toString(), $i));
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

    public function getNodeForPartition(int $partitionIndex) : CacheNode|null
    {
        $partitionHash = $this->hash(value: (string) $partitionIndex);

        return $this->getNodeForKey(
            key: CacheKey::create(key: (string) $partitionHash),
        );
    }

    public function getNodeForKey(CacheKey $key) : CacheNode|null
    {
        if ($this->ring === []) {
            return null;
        }

        $keyHash = $this->hash(value: $key->fullKey());

        $closestNode     = null;
        $closestPosition = null;

        foreach ($this->ring as $position => $node) {
            if ($position < $keyHash) {
                continue;
            }
            if ($closestNode !== null && $position >= $closestPosition) {
                continue;
            }
            $closestNode     = $node;
            $closestPosition = $position;
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
        return $this->nodes === [];
    }
}
