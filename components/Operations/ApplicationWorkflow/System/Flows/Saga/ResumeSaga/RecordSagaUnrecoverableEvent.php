<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;

final readonly class RecordSagaUnrecoverableEvent
{
    public function __construct(private object $inspect) {}

    public function record(
        SagaInstance $sagaInstance,
        string $reason,
    ): void {
        $this->inspect->record(
            SagaRuntimeEvent::create(
                sagaId  : $sagaInstance->id,
                sagaName: $sagaInstance->definitionName,
                type    : 'saga_unrecoverable',
                payload : ['reason' => $reason],
            ),
        );
    }
}
