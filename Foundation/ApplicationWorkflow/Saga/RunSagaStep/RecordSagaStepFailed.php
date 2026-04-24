<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\RunSagaStep;

use Avax\ApplicationWorkflow\Saga\StoreSagaState\SagaEvent;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;
use Throwable;

/**
 * RecordSagaStepFailed - appends an event before a failed step is returned to the caller.
 */
final readonly class RecordSagaStepFailed
{
    public function record(StoreSagaState $storeSagaState, string $instanceId, string $stepName, string $correlationId, Throwable $failure) : SagaEvent
    {
        return $storeSagaState->appendEvent(event: new SagaEvent(
                                                       id           : 'saga-event-' . bin2hex(string: random_bytes(length: 8)),
                                                       instanceId   : $instanceId,
                                                       type         : 'saga.step.failed',
                                                       payload      : ['step' => $stepName, 'failure' => $failure->getMessage()],
                                                       correlationId: $correlationId
                                                   ));
    }
}
