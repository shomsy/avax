<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\InspectSaga;

/**
 * RecordSagaEvent - appends diagnostics-safe saga runtime events.
 */
final readonly class RecordSagaEvent
{
    /**
     * @param list<SagaRuntimeEvent> $events
     */
    public function record(array &$events, SagaRuntimeEvent $sagaRuntimeEvent) : SagaRuntimeEvent
    {
        $events[] = $sagaRuntimeEvent;

        return $sagaRuntimeEvent;
    }
}
