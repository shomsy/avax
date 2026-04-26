<?php

declare(strict_types=1);

namespace components\ApplicationWorkflow\Saga\RunSagaStep;

use components\ApplicationWorkflow\Saga\StoreSagaState\SagaEvent;
use components\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;
use Random\RandomException;

/**
 * RecordSagaStepCompleted - appends an event after a saga step succeeds.
 */
final readonly class RecordSagaStepCompleted
{
    /**
     * @throws RandomException
     */
    public function record(StoreSagaState $storeSagaState, string $instanceId, string $stepName, string $correlationId) : SagaEvent
    {
        return $storeSagaState->appendEvent(event: new SagaEvent(
                                                       id           : 'saga-event-' . bin2hex(string: random_bytes(length: 8)),
                                                       instanceId   : $instanceId,
                                                       type         : 'saga.step.completed',
                                                       payload      : ['step' => $stepName],
                                                       correlationId: $correlationId
                                                   ));
    }
}
