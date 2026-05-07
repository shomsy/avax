<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga;

/**
 * TraceSagaFailure - extracts failure events from a saga timeline.
 */
final readonly class TraceSagaFailure
{
    /**
     * @return list<SagaRuntimeEvent>
     */
    public function trace(SagaTimeline $sagaTimeline) : array
    {
        return array_values(array: array_filter(
                                       array   : $sagaTimeline->events,
                                       callback: static fn (SagaRuntimeEvent $sagaRuntimeEvent) : bool => str_contains(haystack: $sagaRuntimeEvent->type, needle: 'failed'),
                                   ));
    }
}
