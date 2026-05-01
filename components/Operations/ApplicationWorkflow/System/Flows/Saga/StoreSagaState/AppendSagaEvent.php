<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

/**
 * AppendSagaEvent - appends ordered saga event evidence for recovery and diagnostics.
 */
final readonly class AppendSagaEvent
{
    /**
     * @param array<string, list<SagaEvent>> $events
     */
    public function append(array &$events, SagaEvent $event): SagaEvent
    {
        $events[$event->instanceId] ??= [];
        $events[$event->instanceId][] = $event;

        return $event;
    }
}
