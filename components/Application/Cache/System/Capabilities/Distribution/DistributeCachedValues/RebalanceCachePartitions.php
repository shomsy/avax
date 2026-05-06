<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

final readonly class RebalanceCachePartitions
{
    public function __construct(
        private ConsistentHashRing $consistentHashRing,
        private int $partitionCount = 256,
    ) {
    }

    /**
     * @return array<int, array{from: string|null, to: string}>
     */
    public function addNode(CacheNode $cacheNode): array
    {
        $oldDistribution = $this->rebalance();

        $this->consistentHashRing->addNode(cacheNode: $cacheNode);

        $newDistribution = $this->rebalance();

        return $this->calculateMoves(old: $oldDistribution, new: $newDistribution);
    }

    /**
     * @return array<int, string>
     */
    public function rebalance(): array
    {
        $moves = [];

        for ($i = 0; $i < $this->partitionCount; $i++) {
            $node = $this->consistentHashRing->getNodeForPartition(partitionIndex: $i);

            if ($node instanceof CacheNode) {
                $moves[$i] = $node->id->toString();
            }
        }

        return $moves;
    }

    /**
     * @param  array<int, string>  $old
     * @param  array<int, string>  $new
     * @return array<int, array{from: string|null, to: string}>
     */
    private function calculateMoves(array $old, array $new): array
    {
        $moves = [];

        foreach ($new as $partition => $nodeId) {
            if (! isset($old[$partition]) || $old[$partition] !== $nodeId) {
                $moves[$partition] = [
                    'from' => $old[$partition] ?? null,
                    'to' => $nodeId,
                ];
            }
        }

        return $moves;
    }

    /**
     * @return array<int, array{from: string|null, to: string}>
     */
    public function removeNode(CacheNodeId $cacheNodeId): array
    {
        $oldDistribution = $this->rebalance();

        $this->consistentHashRing->removeNode(cacheNodeId: $cacheNodeId);

        $newDistribution = $this->rebalance();

        return $this->calculateMoves(old: $oldDistribution, new: $newDistribution);
    }
}
