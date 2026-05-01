<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga;

/**
 * SagaTimeline - ordered runtime events for one saga instance.
 */
final readonly class SagaTimeline
{
    /**
     * @param list<SagaRuntimeEvent> $events
     */
    public function __construct(public string $instanceId, public array $events) {}
}
