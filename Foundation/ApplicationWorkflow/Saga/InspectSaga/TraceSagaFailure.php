<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\InspectSaga;

/**
 * TraceSagaFailure - extracts failure events from a saga timeline.
 */
final readonly class TraceSagaFailure
{
    /**
     * @return list<SagaRuntimeEvent>
     */
    public function trace(SagaTimeline $timeline) : array
    {
        return array_values(array: array_filter(
                                       array   : $timeline->events,
                                       callback: static fn (SagaRuntimeEvent $event) : bool => str_contains(haystack: $event->type, needle: 'failed')
                                   ));
    }
}
