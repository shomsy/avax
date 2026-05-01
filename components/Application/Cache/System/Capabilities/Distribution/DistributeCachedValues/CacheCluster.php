<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final class CacheCluster
{
    /** @var array<string, CacheNode> */
    private array $nodes = [];

    private readonly ConsistentHashRing $consistentHashRing;

    private readonly DetectUnhealthyCacheNode $detectUnhealthyCacheNode;

    public function __construct(
        int $virtualNodes = 150,
        int $failureThreshold = 3,
    )
    {
        $this->consistentHashRing = new ConsistentHashRing(virtualNodes: $virtualNodes);
        $this->detectUnhealthyCacheNode = new DetectUnhealthyCacheNode(failureThreshold: $failureThreshold);
    }

    public function getNode(CacheNodeId $cacheNodeId) : ?CacheNode
    {
        return $this->nodes[$cacheNodeId->toString()] ?? null;
    }

    public function getHealthyNodes() : array
    {
        return array_filter($this->nodes, static fn (CacheNode $cacheNode) : bool => $cacheNode->isHealthy());
    }

    public function recordSuccess(CacheNodeId $cacheNodeId) : void
    {
        $cacheNodeStatus = $this->detectUnhealthyCacheNode->recordSuccess(nodeId: $cacheNodeId);

        if ($cacheNodeStatus === CacheNodeStatus::HEALTHY && isset($this->nodes[$cacheNodeId->toString()])) {
            $this->nodes[$cacheNodeId->toString()] = $this->nodes[$cacheNodeId->toString()]->withStatus(status: $cacheNodeStatus);
            $this->consistentHashRing->addNode(node: $this->nodes[$cacheNodeId->toString()]);
        }
    }

    public function addNode(CacheNode $cacheNode) : self
    {
        $this->nodes[$cacheNode->id->toString()] = $cacheNode;
        $this->consistentHashRing->addNode(node: $cacheNode);

        return $this;
    }

    public function recordFailure(CacheNodeId $cacheNodeId) : void
    {
        $cacheNodeStatus = $this->detectUnhealthyCacheNode->recordFailure(nodeId: $cacheNodeId);

        if (isset($this->nodes[$cacheNodeId->toString()])) {
            $this->nodes[$cacheNodeId->toString()] = $this->nodes[$cacheNodeId->toString()]->withStatus(status: $cacheNodeStatus);
            $this->consistentHashRing->removeNode(nodeId: $cacheNodeId);
        }
    }

    public function removeNode(CacheNodeId $cacheNodeId) : self
    {
        $nodeIdStr = $cacheNodeId->toString();

        if (isset($this->nodes[$nodeIdStr])) {
            unset($this->nodes[$nodeIdStr]);
            $this->consistentHashRing->removeNode(nodeId: $cacheNodeId);
        }

        return $this;
    }

    public function getNodeForKey(CacheKey $cacheKey) : ?CacheNode
    {
        return $this->consistentHashRing->getNodeForKey(key: $cacheKey);
    }

    public function nodeCount() : int
    {
        return count($this->nodes);
    }
}
