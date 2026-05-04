<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\Flows\EstimateQueuePressure;

use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityModel;

final readonly class EstimateQueuePressure
{
    public function __invoke(CapacityModel $model): array
    {
        $incomingPerSecond = $model->writesPerSecond * $model->fanoutSize;
        $consumptionPerSecond = $model->consumerCount * 1000;
        $lagSeconds = $incomingPerSecond > 0
            ? $model->queueDepth / max(1, $incomingPerSecond - $consumptionPerSecond)
            : 0;

        return [
            'incomingPerSecond' => $incomingPerSecond,
            'consumptionPerSecond' => $consumptionPerSecond,
            'queueLagSeconds' => max(0, $lagSeconds),
            'backlogSeconds' => $model->queueDepth / max(1, $incomingPerSecond),
        ];
    }
}
