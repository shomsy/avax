<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\LatencyBudget\System\Flows\AllocateLatency;

use Avax\Components\SystemDesign\LatencyBudget\System\PublicSurface\LatencyBudget;

final readonly class AllocateLatency
{
    /**
     * @return array<string, int>
     */
    public function __invoke(LatencyBudget $budget, int $totalAvailable): array
    {
        return [
            'http' => (int)($totalAvailable * 0.10),
            'cache' => (int)($totalAvailable * 0.15),
            'database' => (int)($totalAvailable * 0.40),
            'queue' => (int)($totalAvailable * 0.25),
            'external' => (int)($totalAvailable * 0.10),
        ];
    }
}
