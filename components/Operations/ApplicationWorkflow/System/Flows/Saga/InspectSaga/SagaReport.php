<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga;

/**
 * SagaReport - diagnostics-safe report for a saga instance.
 */
final readonly class SagaReport
{
    /**
     * @param  list<SagaRuntimeEvent>  $events
     */
    public function __construct(public string $instanceId, public array $events) {}
}
