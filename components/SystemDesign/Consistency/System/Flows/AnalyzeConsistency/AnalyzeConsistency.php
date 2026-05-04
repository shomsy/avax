<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Consistency\System\Flows\AnalyzeConsistency;

use Avax\Components\SystemDesign\Consistency\System\PublicSurface\ConsistencyLevel;

final readonly class AnalyzeConsistency
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ConsistencyLevel $level): array
    {
        return [
            'level' => $level->value,
            'latencyMs' => match ($level) {
                ConsistencyLevel::EVENTUAL => 5000,
                ConsistencyLevel::CAUSAL => 100,
                ConsistencyLevel::READ_YOUR_WRITES => 50,
                ConsistencyLevel::SEQUENTIAL => 20,
                ConsistencyLevel::LINEARIZABLE => 10,
            },
            'complexity' => match ($level) {
                ConsistencyLevel::EVENTUAL => 'low',
                ConsistencyLevel::CAUSAL => 'medium',
                ConsistencyLevel::READ_YOUR_WRITES => 'medium',
                ConsistencyLevel::SEQUENTIAL => 'high',
                ConsistencyLevel::LINEARIZABLE => 'very-high',
            },
        ];
    }
}
