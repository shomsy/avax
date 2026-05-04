<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Replication\System\Flows\AnalyzeReplication;

use Avax\Components\SystemDesign\Replication\System\PublicSurface\ReplicationStrategy;

final readonly class AnalyzeReplication
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ReplicationStrategy $strategy, int $replicaCount): array
    {
        return [
            'strategy' => $strategy->value,
            'replicaCount' => $replicaCount,
            'writeAvailability' => match ($strategy) {
                ReplicationStrategy::SINGLE_LEADER => $replicaCount > 1 ? 'quorum' : 'single',
                ReplicationStrategy::MULTI_LEADER => 'any',
                ReplicationStrategy::LEADERSLESS => 'quorum',
            },
            'readAvailability' => match ($strategy) {
                ReplicationStrategy::SINGLE_LEADER => 'any',
                ReplicationStrategy::MULTI_LEADER => 'any',
                ReplicationStrategy::LEADERSLESS => 'quorum',
            },
            'consistency' => match ($strategy) {
                ReplicationStrategy::SINGLE_LEADER => 'strong',
                ReplicationStrategy::MULTI_LEADER => 'eventual',
                ReplicationStrategy::LEADERSLESS => 'eventual',
            },
        ];
    }
}
