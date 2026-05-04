<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\Flows\EstimateLatencyBudget;

final readonly class EstimateLatencyBudget
{
    public function __invoke(int $totalBudgetMs): array
    {
        $http = (int)($totalBudgetMs * 0.10);
        $cache = (int)($totalBudgetMs * 0.15);
        $db = (int)($totalBudgetMs * 0.40);
        $queue = (int)($totalBudgetMs * 0.25);
        $external = (int)($totalBudgetMs * 0.10);

        return [
            'httpMs' => $http,
            'cacheMs' => $cache,
            'databaseMs' => $db,
            'queueMs' => $queue,
            'externalMs' => $external,
            'totalMs' => $http + $cache + $db + $queue + $external,
        ];
    }
}
