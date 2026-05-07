<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StartSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StoreSagaState\SagaEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StoreSagaState\StoreSagaState;
use Random\RandomException;

/**
 * RecordSagaStarted - writes the start event after the initial saga state is saved.
 */
final readonly class RecordSagaStarted
{
    /**
     * @throws RandomException
     */
    public function record(StoreSagaState $storeSagaState, SagaInstance $sagaInstance) : SagaEvent
    {
        return $storeSagaState->appendEvent(event: new SagaEvent(
                                                       id           : 'saga-event-' . bin2hex(string: random_bytes(length: 8)),
                                                       type         : 'saga.started',
                                                       payload      : ['definition' => $sagaInstance->definitionName, 'current_step' => $sagaInstance->currentStepName],
                                                       instanceId   : $sagaInstance->id,
                                                       correlationId: $sagaInstance->correlationId->value,
                                                   ));
    }
}
