<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstanceStatus;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;

final readonly class ResumeSaga
{
    public function findRecoverable(): array
    {
        return [];
    }

    public function resume(
        SagaInstance   $sagaInstance,
        SagaDefinition $sagaDefinition,
    ): SagaInstance {
        if ($sagaInstance->status !== SagaInstanceStatus::FAILED) {
            throw new SagaRecoveryFailure(
                message: sprintf('Saga %s is not failed, cannot resume.', $sagaInstance->id),
            );
        }

        $failedStep = $sagaInstance->currentStepName;
        if ($failedStep === null) {
            throw new SagaRecoveryFailure(
                message: sprintf('Cannot determine failed step for saga %s.', $sagaInstance->id),
            );
        }

        $stepDef = $sagaDefinition->getStep(name: $failedStep);
        if (! $stepDef instanceof SagaStepDefinition) {
            throw new SagaRecoveryFailure(
                message: sprintf('Step %s not found in definition.', $failedStep),
            );
        }

        return $sagaInstance->retry();
    }

    public function canRecover(SagaInstance $sagaInstance) : bool
    {
        return $sagaInstance->status === SagaInstanceStatus::FAILED;
    }
}

final readonly class SagaRecoveryPlan
{
    private function __construct(public string $sagaId, public ?string $failedStep, public int $attemptNumber, public array $recoverySteps, public bool $isRecoverable) {
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
            'saga_id'     => $this->sagaId,
            'failed_step' => $this->failedStep,
            'attempt_number' => $this->attemptNumber,
            'recovery_steps' => $this->recoverySteps,
            'is_recoverable' => $this->isRecoverable,
        ];
    }
}
