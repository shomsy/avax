<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga;

/**
 * BuildSagaTimeline - selects ordered runtime events for one saga instance.
 */
final readonly class BuildSagaTimeline
{
    /**
     * @param list<SagaRuntimeEvent> $events
     */
    public function build(array $events, string $instanceId) : SagaTimeline
    {
        return new SagaTimeline(
            events    : array_values(array: array_filter(
                                                array   : $events,
                                                callback: static fn (SagaRuntimeEvent $sagaRuntimeEvent) : bool => $sagaRuntimeEvent->instanceId === $instanceId,
                                            )),
            instanceId: $instanceId,
        );
    }
}
