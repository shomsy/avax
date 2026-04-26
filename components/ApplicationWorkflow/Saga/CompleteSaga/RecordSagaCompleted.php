<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\CompleteSaga;

use Avax\ApplicationWorkflow\Saga\StoreSagaState\SagaEvent;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;
use Random\RandomException;

/**
 * RecordSagaCompleted - appends the completion event for a terminal saga state.
 */
final readonly class RecordSagaCompleted
{
    /**
     * @throws RandomException
     */
    public function record(StoreSagaState $storeSagaState, string $instanceId, string $correlationId) : SagaEvent
    {
        return $storeSagaState->appendEvent(event: new SagaEvent(
                                                       id           : 'saga-event-' . bin2hex(string: random_bytes(length: 8)),
                                                       instanceId   : $instanceId,
                                                       type         : 'saga.completed',
                                                       correlationId: $correlationId
                                                   ));
    }
}
