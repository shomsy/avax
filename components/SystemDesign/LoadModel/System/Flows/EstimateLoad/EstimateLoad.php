<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\LoadModel\System\Flows\EstimateLoad;

use Avax\Components\SystemDesign\LoadModel\System\PublicSurface\LoadModel;

final readonly class EstimateLoad
{
    public function __invoke(LoadModel $model): array
    {
        $stableRps = $model->concurrentUsers * $model->requestsPerUser / $model->thinkTimeSeconds;
        $peakRps = $stableRps * 1.5;

        return [
            'concurrentUsers' => $model->concurrentUsers,
            'stableRequestsPerSecond' => (int)$stableRps,
            'peakRequestsPerSecond' => (int)$peakRps,
            'rampUpSeconds' => $model->rampUpSeconds,
        ];
    }
}
