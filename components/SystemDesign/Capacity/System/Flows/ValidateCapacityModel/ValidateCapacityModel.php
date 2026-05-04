<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\Flows\ValidateCapacityModel;

use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityBudget;
use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityModel;
use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityReport;
use Avax\Components\SystemDesign\Capacity\System\PublicSurface\CapacityViolation;

final readonly class ValidateCapacityModel
{
    /**
     * @return CapacityReport
     */
    public function __invoke(CapacityModel $model, CapacityBudget $budget): CapacityReport
    {
        $violations = [];

        if ($model->requestsPerSecond > $budget->maxRequestsPerSecond) {
            $violations[] = new CapacityViolation(
                type: 'traffic',
                message: 'Requests per second exceeds budget',
                actual: (float)$model->requestsPerSecond,
                allowed: (float)$budget->maxRequestsPerSecond,
            );
        }

        if ($model->queueDepth > $budget->maxQueueDepth) {
            $violations[] = new CapacityViolation(
                type: 'queue',
                message: 'Queue depth exceeds budget',
                actual: (float)$model->queueDepth,
                allowed: (float)$budget->maxQueueDepth,
            );
        }

        return new CapacityReport(model: $model, budget: $budget, violations: $violations);
    }
}
