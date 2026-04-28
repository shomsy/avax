<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

/**
 * ScheduleNextSagaStep - names the next step that should run after a completed step.
 */
final readonly class ScheduleNextSagaStep
{
    public function schedule(string|null $nextStepName) : string|null
    {
        return $nextStepName;
    }
}
