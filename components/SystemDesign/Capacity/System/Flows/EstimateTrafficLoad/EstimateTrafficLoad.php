<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\Flows\EstimateTrafficLoad;

use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityModel;

final readonly class EstimateTrafficLoad
{
    public function __invoke(CapacityModel $model): array
    {
        $peakRps = $model->requestsPerSecond * $model->peakTrafficMultiplier;
        $readRps = (int)($model->requestsPerSecond * ($model->readWriteRatio / (1 + $model->readWriteRatio)));
        $writeRps = $model->requestsPerSecond - $readRps;

        return [
            'peakRequestsPerSecond' => $peakRps,
            'readsPerSecond' => $readRps,
            'writesPerSecond' => $writeRps,
            'burstRequests' => $peakRps * $model->burstWindow,
            'fanoutTotalReads' => $readRps * $model->fanoutSize,
        ];
    }
}
