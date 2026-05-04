<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Partitioning\System\Flows\DesignPartitioning;

use Avax\Components\SystemDesign\Partitioning\System\PublicSurface\PartitioningStrategy;

final readonly class DesignPartitioning
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(PartitioningStrategy $strategy, int $shardCount): array
    {
        return [
            'strategy' => $strategy->value,
            'shardCount' => $shardCount,
            'hotSpotRisk' => match ($strategy) {
                PartitioningStrategy::RANGE => 'high',
                PartitioningStrategy::HASH => 'low',
                PartitioningStrategy::LIST => 'medium',
                PartitioningStrategy::COMPOSITE => 'low',
            },
            'rebalanceComplexity' => match ($strategy) {
                PartitioningStrategy::RANGE => 'medium',
                PartitioningStrategy::HASH => 'high',
                PartitioningStrategy::LIST => 'low',
                PartitioningStrategy::COMPOSITE => 'very-high',
            },
        ];
    }
}
