<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

/**
 * ReadSagaEvents - returns the event history for one saga instance in append order.
 */
final readonly class ReadSagaEvents
{
    /**
     * @param array<string, list<SagaEvent>> $events
     *
     * @return list<SagaEvent>
     */
    public function read(array $events, string $instanceId) : array
    {
        return $events[$instanceId] ?? [];
    }
}
