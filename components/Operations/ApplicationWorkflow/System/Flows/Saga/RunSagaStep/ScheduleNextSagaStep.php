<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

/**
 * ScheduleNextSagaStep - names the next step that should run after a completed step.
 */
final readonly class ScheduleNextSagaStep
{
    public function schedule(?string $nextStepName) : ?string
    {
        return $nextStepName;
    }
}
