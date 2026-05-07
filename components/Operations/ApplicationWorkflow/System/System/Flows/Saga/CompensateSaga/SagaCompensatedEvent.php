<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\CompensateSaga;

final readonly class SagaCompensatedEvent
{
    public function __construct(
        public string $sagaId,
        public array  $compensatedSteps,
        public float  $timestamp,
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
