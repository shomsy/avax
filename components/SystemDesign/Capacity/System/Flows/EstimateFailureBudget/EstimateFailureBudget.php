<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\Flows\EstimateFailureBudget;

final readonly class EstimateFailureBudget
{
    public function __invoke(float $availabilityTarget): array
    {
        $downtimePerYear = (1 - $availabilityTarget) * 525600;
        $downtimePerMonth = $downtimePerYear / 12;
        $errorBudget = 1 - $availabilityTarget;

        return [
            'availabilityTarget' => $availabilityTarget,
            'errorBudget' => $errorBudget,
            'downtimeMinutesPerYear' => round($downtimePerYear, 2),
            'downtimeMinutesPerMonth' => round($downtimePerMonth, 2),
            'errorsPerMillion' => (int)($errorBudget * 1000000),
        ];
    }
}
