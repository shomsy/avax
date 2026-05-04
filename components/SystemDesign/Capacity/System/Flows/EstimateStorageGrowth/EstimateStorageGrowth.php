<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\Flows\EstimateStorageGrowth;

use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityModel;

final readonly class EstimateStorageGrowth
{
    public function __invoke(CapacityModel $model): array
    {
        $dailyWrites = $model->writesPerSecond * 86400;
        $dailyStorageBytes = $dailyWrites * $model->averageObjectSize;
        $retentionStorage = $dailyStorageBytes * $model->retentionDays;

        return [
            'dailyWrites' => $dailyWrites,
            'dailyStorageMb' => (int)($dailyStorageBytes / 1048576),
            'retentionStorageMb' => (int)($retentionStorage / 1048576),
            'monthlyGrowthMb' => (int)(($dailyStorageBytes * 30) / 1048576),
        ];
    }
}
