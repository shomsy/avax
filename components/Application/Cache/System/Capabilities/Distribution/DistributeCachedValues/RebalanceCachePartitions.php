<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

final readonly class RebalanceCachePartitions
{
    public function __construct(
        private ConsistentHashRing $ring,
        private int                $partitionCount = 256
    ) {}

    public function addNode(CacheNode $node) : array
    {
        $oldDistribution = $this->rebalance();

        $this->ring->addNode(node: $node);

        $newDistribution = $this->rebalance();

        return $this->calculateMoves(old: $oldDistribution, new: $newDistribution);
    }

    public function rebalance() : array
    {
        $moves = [];

        for ($i = 0; $i < $this->partitionCount; $i++) {
            $node = $this->ring->getNodeForPartition(partitionIndex: $i);

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

    public function removeNode(CacheNodeId $nodeId) : array
    {
        $oldDistribution = $this->rebalance();

        $this->ring->removeNode(nodeId: $nodeId);

        $newDistribution = $this->rebalance();

        return $this->calculateMoves(old: $oldDistribution, new: $newDistribution);
    }
}