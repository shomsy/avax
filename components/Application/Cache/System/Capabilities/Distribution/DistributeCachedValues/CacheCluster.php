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
    ) {
        $this->consistentHashRing = new ConsistentHashRing(virtualNodes: $virtualNodes);
        $this->detectUnhealthyCacheNode = new DetectUnhealthyCacheNode(failureThreshold: $failureThreshold);
    }

    public function getNode(CacheNodeId $cacheNodeId) : CacheNode|null
    {
        return $this->nodes[$cacheNodeId->toString()] ?? null;
    }

    /**
     * @return array<string, CacheNode>
     */
    public function getHealthyNodes(): array
    {
        return array_filter($this->nodes, static fn (CacheNode $cacheNode): bool => $cacheNode->isHealthy());
    }

    public function recordSuccess(CacheNodeId $cacheNodeId): void
    {
        $cacheNodeStatus = $this->detectUnhealthyCacheNode->recordSuccess(cacheNodeId: $cacheNodeId);

        if ($cacheNodeStatus === CacheNodeStatus::HEALTHY && isset($this->nodes[$cacheNodeId->toString()])) {
            $this->nodes[$cacheNodeId->toString()] = $this->nodes[$cacheNodeId->toString()]->withStatus(cacheNodeStatus: $cacheNodeStatus);
            $this->consistentHashRing->addNode(cacheNode: $this->nodes[$cacheNodeId->toString()]);
        }
    }

    public function addNode(CacheNode $cacheNode): self
    {
        $this->nodes[$cacheNode->id->toString()] = $cacheNode;
        $this->consistentHashRing->addNode(cacheNode: $cacheNode);

        return $this;
    }

    public function recordFailure(CacheNodeId $cacheNodeId): void
    {
        $cacheNodeStatus = $this->detectUnhealthyCacheNode->recordFailure(cacheNodeId: $cacheNodeId);

        if (isset($this->nodes[$cacheNodeId->toString()])) {
            $this->nodes[$cacheNodeId->toString()] = $this->nodes[$cacheNodeId->toString()]->withStatus(cacheNodeStatus: $cacheNodeStatus);
            $this->consistentHashRing->removeNode(cacheNodeId: $cacheNodeId);
        }
    }

    public function removeNode(CacheNodeId $cacheNodeId): self
    {
        $nodeIdStr = $cacheNodeId->toString();

        if (isset($this->nodes[$nodeIdStr])) {
            unset($this->nodes[$nodeIdStr]);
            $this->consistentHashRing->removeNode(cacheNodeId: $cacheNodeId);
        }

        return $this;
    }

    public function getNodeForKey(CacheKey $cacheKey) : CacheNode|null
    {
        return $this->consistentHashRing->getNodeForKey(cacheKey: $cacheKey);
    }

    public function nodeCount(): int
    {
        return count($this->nodes);
    }
}
