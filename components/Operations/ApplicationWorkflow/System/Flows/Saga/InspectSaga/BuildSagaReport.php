<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga;

/**
 * BuildSagaReport - converts a timeline into a diagnostics report without exposing sensitive payload keys.
 */
final readonly class BuildSagaReport
{
    public function build(SagaTimeline $sagaTimeline) : SagaReport
    {
        return new SagaReport(events: $sagaTimeline->events, instanceId: $sagaTimeline->instanceId);
    }
}
