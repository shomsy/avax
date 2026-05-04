<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\Flows\EstimateCacheEffectiveness;

use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityModel;

final readonly class EstimateCacheEffectiveness
{
    public function __invoke(CapacityModel $model): array
    {
        $readsPerSecond = $model->readsPerSecond > 0
            ? $model->readsPerSecond
            : (int)($model->requestsPerSecond * 0.8);

        $cachedHits = (int)($readsPerSecond * $model->cacheHitRatio);
        $dbHits = $readsPerSecond - $cachedHits;

        $missPenalty = 50;
        $avgLatencyMs = ($cachedHits * 1 + $dbHits * $missPenalty) / max(1, $readsPerSecond);

        return [
            'readsPerSecond' => $readsPerSecond,
            'cacheHits' => $cachedHits,
            'dbHits' => $dbHits,
            'hitRatio' => $model->cacheHitRatio,
            'estimatedAvgLatencyMs' => round($avgLatencyMs, 2),
        ];
    }
}
