<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

final readonly class RebalanceCachePartitions
{
    public function __construct(
        private ConsistentHashRing $consistentHashRing,
        private int                $partitionCount = 256,
    ) {}

    public function addNode(CacheNode $cacheNode) : array
    {
        $oldDistribution = $this->rebalance();

        $this->consistentHashRing->addNode(node: $cacheNode);

        $newDistribution = $this->rebalance();

        return $this->calculateMoves(old: $oldDistribution, new: $newDistribution);
    }

    public function rebalance() : array
    {
        $moves = [];

        for ($i = 0; $i < $this->partitionCount; $i++) {
            $node = $this->consistentHashRing->getNodeForPartition(partitionIndex: $i);

            if ($node !== null) {
                $moves[$i] = $node->id->toString();
            }
        }

        return $moves;
    }

    private function calculateMoves(array $old, array $new) : array
    {
        $moves = [];

        foreach ($new as $partition => $nodeId) {
            if (! isset($old[$partition]) || $old[$partition] !== $nodeId) {
                $moves[$partition] = [
                    'from' => $old[$partition] ?? null,
                    'to'   => $nodeId,
                ];
            }
        }

        return $moves;
    }

    public function removeNode(CacheNodeId $cacheNodeId) : array
    {
        $oldDistribution = $this->rebalance();

        $this->consistentHashRing->removeNode(nodeId: $cacheNodeId);

        $newDistribution = $this->rebalance();

        return $this->calculateMoves(old: $oldDistribution, new: $newDistribution);
    }
}
