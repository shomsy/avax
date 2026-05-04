<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Sharding\System\Flows\AnalyzeSharding;

use Avax\Components\SystemDesign\Sharding\System\PublicSurface\ShardingKey;

final readonly class AnalyzeSharding
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ShardingKey $key, int $totalRecords): array
    {
        $recordsPerShard = (int)ceil($totalRecords / $key->shardCount);

        return [
            'shardKey' => $key->field,
            'shardCount' => $key->shardCount,
            'totalRecords' => $totalRecords,
            'recordsPerShard' => $recordsPerShard,
            'rebalanceCost' => match (true) {
                $key->shardCount < 4 => 'low',
                $key->shardCount < 16 => 'medium',
                default => 'high',
            },
        ];
    }
}
