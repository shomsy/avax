<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompensateSaga;

final readonly class CompensationCompletedRecord
{
    public function __construct(
        public string $sagaId,
        public array $completedSteps,
        public array $results,
        public float $recordedAt,
    ) {
    }

    public function toMetadata(): array
    {
        return [
            'saga_id' => $this->sagaId,
            'completed_steps' => $this->completedSteps,
            'recorded_at' => $this->recordedAt,
        ];
    }
}
