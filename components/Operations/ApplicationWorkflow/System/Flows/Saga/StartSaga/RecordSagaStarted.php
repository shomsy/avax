<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\SagaEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use Random\RandomException;

/**
 * RecordSagaStarted - writes the start event after the initial saga state is saved.
 */
final readonly class RecordSagaStarted
{
    /**
     * @throws RandomException
     */
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
