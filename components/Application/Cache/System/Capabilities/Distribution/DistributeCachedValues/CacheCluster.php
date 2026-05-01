<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final class CacheCluster
{
    /** @var array<CacheNodeId, CacheNode> */
    private array $nodes = [];

    private readonly ConsistentHashRing $consistentHashRing;

    private readonly DetectUnhealthyCacheNode $detectUnhealthyCacheNode;

    public function __construct(
        int $virtualNodes = 150,
        int $failureThreshold = 3,
    )
    {
        $this->consistentHashRing       = new ConsistentHashRing(virtualNodes: $virtualNodes);
        $this->detectUnhealthyCacheNode = new DetectUnhealthyCacheNode(failureThreshold: $failureThreshold);
    }

    public function getNode(CacheNodeId $nodeId) : CacheNode|null
    {
        return $this->nodes[$nodeId->toString()] ?? null;
    }

    public function getHealthyNodes() : array
    {
        return array_filter($this->nodes, static fn (CacheNode $cacheNode) : bool => $cacheNode->isHealthy());
    }

    public function recordSuccess(CacheNodeId $nodeId) : void
    {
        $newStatus = $this->detectUnhealthyCacheNode->recordSuccess(nodeId: $nodeId);

        if ($newStatus === CacheNodeStatus::HEALTHY && isset($this->nodes[$nodeId->toString()])) {
            $this->nodes[$nodeId->toString()] = $this->nodes[$nodeId->toString()]->withStatus(status: $newStatus);
            $this->consistentHashRing->addNode(node: $this->nodes[$nodeId->toString()]);
        }
    }

    public function addNode(CacheNode $node) : self
    {
        $this->nodes[$node->id->toString()] = $node;
        $this->consistentHashRing->addNode(node: $node);

        return $this;
    }

    public function recordFailure(CacheNodeId $nodeId) : void
    {
        $newStatus = $this->detectUnhealthyCacheNode->recordFailure(nodeId: $nodeId);

        if (isset($this->nodes[$nodeId->toString()])) {
            $this->nodes[$nodeId->toString()] = $this->nodes[$nodeId->toString()]->withStatus(status: $newStatus);
            $this->consistentHashRing->removeNode(nodeId: $nodeId);
        }
    }

    public function removeNode(CacheNodeId $nodeId) : self
    {
        $nodeIdStr = $nodeId->toString();

        if (isset($this->nodes[$nodeIdStr])) {
            unset($this->nodes[$nodeIdStr]);
            $this->consistentHashRing->removeNode(nodeId: $nodeId);
        }

        return $this;
    }

    public function getNodeForKey(CacheKey $key) : CacheNode|null
    {
        return $this->consistentHashRing->getNodeForKey(key: $key);
    }

    public function nodeCount() : int
    {
        return count($this->nodes);
    }
}
