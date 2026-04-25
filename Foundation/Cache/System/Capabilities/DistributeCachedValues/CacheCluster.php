<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\DistributeCachedValues;

final class CacheCluster
{
    /** @var array<CacheNodeId, CacheNode> */
    private array                    $nodes = [];
    private ConsistentHashRing       $ring;
    private DetectUnhealthyCacheNode $healthChecker;

    public function __construct(
        private int $virtualNodes = 150
    )
    {
        $this->ring          = new ConsistentHashRing($virtualNodes);
        $this->healthChecker = new DetectUnhealthyCacheNode();
    }

    public function getNode(CacheNodeId $nodeId) : ?CacheNode
    {
        return $this->nodes[$nodeId->toString()] ?? null;
    }

    public function getHealthyNodes() : array
    {
        return array_filter($this->nodes, fn (CacheNode $node) => $node->isHealthy());
    }

    public function recordSuccess(CacheNodeId $nodeId) : void
    {
        $newStatus = $this->healthChecker->recordSuccess($nodeId);

        if ($newStatus === CacheNodeStatus::HEALTHY && isset($this->nodes[$nodeId->toString()])) {
            $this->nodes[$nodeId->toString()] = $this->nodes[$nodeId->toString()]->withStatus($newStatus);
            $this->ring->addNode($this->nodes[$nodeId->toString()]);
        }
    }

    public function addNode(CacheNode $node) : self
    {
        $this->nodes[$node->id->toString()] = $node;
        $this->ring->addNode($node);

        return $this;
    }

    public function recordFailure(CacheNodeId $nodeId) : void
    {
        $newStatus = $this->healthChecker->recordFailure($nodeId);

        if (isset($this->nodes[$nodeId->toString()])) {
            $this->nodes[$nodeId->toString()] = $this->nodes[$nodeId->toString()]->withStatus($newStatus);
            $this->ring->removeNode($nodeId);
        }
    }

    public function removeNode(CacheNodeId $nodeId) : self
    {
        $nodeIdStr = $nodeId->toString();

        if (isset($this->nodes[$nodeIdStr])) {
            unset($this->nodes[$nodeIdStr]);
            $this->ring->removeNode($nodeId);
        }

        return $this;
    }

    public function getNodeForKey(CacheKey $key) : ?CacheNode
    {
        return $this->ring->getNodeForKey($key);
    }

    public function nodeCount() : int
    {
        return count($this->nodes);
    }
}