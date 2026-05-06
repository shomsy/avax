<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

final readonly class SagaRecoveryPlan
{
    private function __construct(
        public string $sagaId,
        public ?string $failedStep,
        public int $attemptNumber,
        public array $recoverySteps,
        public bool $isRecoverable,
    ) {
    }

    public static function create(
        string $sagaId,
        ?string $failedStep,
        int $attemptNumber,
    ): self {
        return new self(
            sagaId       : $sagaId,
            failedStep   : $failedStep,
            attemptNumber: $attemptNumber,
            recoverySteps: [],
            isRecoverable: $failedStep !== null && $attemptNumber < 3,
        );
    }

    public function toArray(): array
    {
        return [
            'saga_id' => $this->sagaId,
            'failed_step' => $this->failedStep,
            'attempt_number' => $this->attemptNumber,
            'recovery_steps' => $this->recoverySteps,
            'is_recoverable' => $this->isRecoverable,
        ];
    }
}
