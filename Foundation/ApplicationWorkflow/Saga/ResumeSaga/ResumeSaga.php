<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\ResumeSaga;

use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstance;
use Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState;

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
        if ($failedInstance->status !== \Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstanceStatus::FAILED) {
            throw new SagaRecoveryFailure(
                sprintf('Saga %s is not failed, cannot resume.', $failedInstance->id)
            );
        }

        $failedStep = $failedInstance->currentStepName;
        if ($failedStep === null) {
            throw new SagaRecoveryFailure(
                sprintf('Cannot determine failed step for saga %s.', $failedInstance->id)
            );
        }

        $stepDef = $definition->getStep($failedStep);
        if ($stepDef === null) {
            throw new SagaRecoveryFailure(
                sprintf('Step %s not found in definition.', $failedStep)
            );
        }

        return $failedInstance->retry();
    }

    public function canRecover(SagaInstance $instance) : bool
    {
        return in_array($instance->status, [
            \Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstanceStatus::FAILED,
        ],              true);
    }
}

final readonly class SagaRecoveryPlan
{
    public string  $sagaId;
    public ?string $failedStep;
    public int     $attemptNumber;
    public array   $recoverySteps;
    public bool    $isRecoverable;

    private function __construct(
        string  $sagaId,
        ?string $failedStep,
        int     $attemptNumber,
        array   $recoverySteps,
        bool    $isRecoverable
    )
    {
        $this->sagaId        = $sagaId;
        $this->failedStep    = $failedStep;
        $this->attemptNumber = $attemptNumber;
        $this->recoverySteps = $recoverySteps;
        $this->isRecoverable = $isRecoverable;
    }

    public static function create(
        string  $sagaId,
        ?string $failedStep,
        int     $attemptNumber
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