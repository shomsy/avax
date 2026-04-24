<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\InspectSaga;

/**
 * BuildSagaReport - converts a timeline into a diagnostics report without exposing sensitive payload keys.
 */
final readonly class BuildSagaReport
{
    public function build(SagaTimeline $timeline) : SagaReport
    {
        return new SagaReport(instanceId: $timeline->instanceId, events: $timeline->events);
    }
}
