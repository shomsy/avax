<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga;

/**
 * RecordSagaEvent - appends diagnostics-safe saga runtime events.
 */
final readonly class RecordSagaEvent
{
    /**
     * @param list<SagaRuntimeEvent> $events
     */
    public function record(array &$events, SagaRuntimeEvent $event): SagaRuntimeEvent
    {
        $events[] = $event;

        return $event;
    }
}
