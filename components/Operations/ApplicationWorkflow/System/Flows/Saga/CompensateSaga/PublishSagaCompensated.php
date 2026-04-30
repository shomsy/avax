<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompensateSaga;

final readonly class PublishSagaCompensated
{
    public function __construct(
        private string $eventBus,
    ) {}

    public function describeResponsibility() : string
    {
        return 'publishes saga compensated event to message bus.';
    }

    public function publish(
        string $sagaId,
        array $compensatedSteps,
    ) : SagaCompensatedEvent
    {
        return new SagaCompensatedEvent(
            sagaId          : $sagaId,
            compensatedSteps: $compensatedSteps,
            timestamp       : microtime(true),
        );
    }

    public function toMetadata() : array
    {
        return ['event_bus' => $this->eventBus];
    }
}

final readonly class SagaCompensatedEvent
{
    public function __construct(
        public string $sagaId,
        public array $compensatedSteps,
        public float $timestamp,
    ) {}

    public function toMetadata() : array
    {
        return [
            'saga_id'           => $this->sagaId,
            'compensated_steps' => $this->compensatedSteps,
            'timestamp'         => $this->timestamp,
        ];
    }
}
