<?php

declare(strict_types=1);

namespace components\ApplicationWorkflow\Saga\CompensateSaga;

final readonly class RecordCompensationCompleted
{
    public function __construct(
        private string $store
    ) {}

    public function describeResponsibility() : string
    {
        return 'records completed compensation to saga store.';
    }

    public function record(
        string $sagaId,
        array  $completedSteps,
        array  $results
    ) : CompensationCompletedRecord
    {
        return new CompensationCompletedRecord(
            sagaId        : $sagaId,
            completedSteps: $completedSteps,
            results       : $results,
            recordedAt    : microtime(true)
        );
    }

    public function toMetadata() : array
    {
        return ['store' => $this->store];
    }
}

final readonly class CompensationCompletedRecord
{
    public function __construct(
        public string $sagaId,
        public array  $completedSteps,
        public array  $results,
        public float  $recordedAt
    ) {}

    public function toMetadata() : array
    {
        return [
            'saga_id'         => $this->sagaId,
            'completed_steps' => $this->completedSteps,
            'recorded_at'     => $this->recordedAt,
        ];
    }
}