<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StartSaga;

use Avax\ApplicationWorkflow\Saga\StoreSagaState\SagaEvent;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;

/**
 * RecordSagaStarted - writes the start event after the initial saga state is saved.
 */
final readonly class RecordSagaStarted
{
    public function record(StoreSagaState $storeSagaState, SagaInstance $instance) : SagaEvent
    {
        return $storeSagaState->appendEvent(event: new SagaEvent(
                                                       id           : 'saga-event-' . bin2hex(string: random_bytes(length: 8)),
                                                       instanceId   : $instance->id,
                                                       type         : 'saga.started',
                                                       payload      : ['definition' => $instance->definitionName, 'current_step' => $instance->currentStepName],
                                                       correlationId: $instance->correlationId->value
                                                   ));
    }
}
