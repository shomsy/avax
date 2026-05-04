<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Availability\System\Flows\CalculateAvailability;

use Avax\Components\SystemDesign\Availability\System\PublicSurface\AvailabilityModel;

final readonly class CalculateAvailability
{
    public function __invoke(AvailabilityModel $model): array
    {
        $nines = strlen((string)$model->target) - 2;
        $downtimeYear = (1 - $model->target) * 525600;

        return [
            'target' => $model->target,
            'nines' => $nines,
            'downtimeMinutesPerYear' => round($downtimeYear, 2),
            'multiRegion' => $model->multiRegion,
        ];
    }
}
