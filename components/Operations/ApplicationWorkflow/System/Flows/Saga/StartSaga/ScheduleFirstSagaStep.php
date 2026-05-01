<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga;

/**
 * ScheduleFirstSagaStep - names the first step that should be run after start state is recorded.
 */
final readonly class ScheduleFirstSagaStep
{
    public function schedule(SagaInstance $sagaInstance) : string
    {
        return $sagaInstance->currentStepName;
    }
}
