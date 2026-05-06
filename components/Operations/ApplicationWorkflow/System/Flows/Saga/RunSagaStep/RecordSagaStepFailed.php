<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\SagaEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use Random\RandomException;
use Throwable;

/**
 * RecordSagaStepFailed - appends an event before a failed step is returned to the caller.
 */
final readonly class RecordSagaStepFailed
{
    /**
     * @throws RandomException
     */
    public function record(StoreSagaState $storeSagaState, string $instanceId, string $stepName, string $correlationId, Throwable $throwable): SagaEvent
    {
        return $storeSagaState->appendEvent(event: new SagaEvent(
            id: 'saga-event-'.bin2hex(string: random_bytes(length: 8)),
            type         : 'saga.step.failed',
            payload: ['step' => $stepName, 'failure' => $throwable->getMessage()],
            instanceId: $instanceId,
            correlationId: $correlationId,
        ));
    }
}
