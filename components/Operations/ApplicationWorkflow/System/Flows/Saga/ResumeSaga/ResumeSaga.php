<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstanceStatus;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;

final readonly class ResumeSaga
{
    public function __construct(
        private StoreSagaState $storeSagaState
    ) {}

    public function findRecoverable() : array
    {
        return [];
    }

    public function resume(
        SagaInstance   $failedInstance,
        SagaDefinition $definition
    ) : SagaInstance
    {
        if ($failedInstance->status !== SagaInstanceStatus::FAILED) {
            throw new SagaRecoveryFailure(
                message: sprintf('Saga %s is not failed, cannot resume.', $failedInstance->id)
            );
        }

        $failedStep = $failedInstance->currentStepName;
        if ($failedStep === null) {
            throw new SagaRecoveryFailure(
                message: sprintf('Cannot determine failed step for saga %s.', $failedInstance->id)
            );
        }

        $stepDef = $definition->getStep(name: $failedStep);
        if ($stepDef === null) {
            throw new SagaRecoveryFailure(
                message: sprintf('Step %s not found in definition.', $failedStep)
            );
        }

        return $failedInstance->retry();
    }

    public function canRecover(SagaInstance $instance) : bool
    {
        return in_array($instance->status, [
            SagaInstanceStatus::FAILED,
        ],              true);
    }
}

final readonly class SagaRecoveryPlan
{
    public string      $sagaId;
    public string|null $failedStep;
    public int         $attemptNumber;
    public array       $recoverySteps;
    public bool        $isRecoverable;

    private function __construct(
        string      $sagaId,
        string|null $failedStep,
        int         $attemptNumber,
        array       $recoverySteps,
        bool        $isRecoverable
    )
    {
        $this->sagaId        = $sagaId;
        $this->failedStep    = $failedStep;
        $this->attemptNumber = $attemptNumber;
        $this->recoverySteps = $recoverySteps;
        $this->isRecoverable = $isRecoverable;
    }

    public static function create(
        string      $sagaId,
        string|null $failedStep,
        int         $attemptNumber
    ) : self
    {
        return new self(
            sagaId       : $sagaId,
            failedStep   : $failedStep,
            attemptNumber: $attemptNumber,
            recoverySteps: [],
            isRecoverable: $failedStep !== null && $attemptNumber < 3
        );
    }

    public function toArray() : array
    {
        return [
            'saga_id'        => $this->sagaId,
            'failed_step'    => $this->failedStep,
            'attempt_number' => $this->attemptNumber,
            'recovery_steps' => $this->recoverySteps,
            'is_recoverable' => $this->isRecoverable,
        ];
    }
}