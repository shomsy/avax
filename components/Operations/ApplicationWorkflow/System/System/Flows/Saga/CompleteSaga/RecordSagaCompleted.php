<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\CompleteSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StoreSagaState\SagaEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\StoreSagaState\StoreSagaState;
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
                                                       type         : 'saga.completed',
                                                       instanceId   : $instanceId,
                                                       correlationId: $correlationId,
                                                   ));
    }
}
