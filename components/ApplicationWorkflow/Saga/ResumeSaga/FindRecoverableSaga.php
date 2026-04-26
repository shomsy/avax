<?php

declare(strict_types=1);

namespace components\ApplicationWorkflow\Saga\ResumeSaga;

use components\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent;
use components\ApplicationWorkflow\Saga\StartSaga\SagaInstance;
use components\ApplicationWorkflow\Saga\StartSaga\SagaInstanceStatus;
use RuntimeException;

final readonly class FindRecoverableSaga
{
    public function __construct(private object $store) {}

    public function find(string|null $tenantId = null, int $limit = 100) : array
    {
        $recoverable = [];
        $allSagas    = $this->store->all();

        foreach ($allSagas as $sagaId => $data) {
            if ($this->isRecoverable(data: $data, tenantId: $tenantId)) {
                $recoverable[] = $sagaId;
                if (count($recoverable) >= $limit) {
                    break;
                }
            }
        }

        return $recoverable;
    }

    private function isRecoverable(array $data, string|null $tenantId) : bool
    {
        if (($data['status'] ?? '') !== 'failed') {
            return false;
        }

        if ($tenantId !== null && ($data['tenant_id'] ?? '') !== $tenantId) {
            return false;
        }

        $currentStep = $data['current_step_name'] ?? null;
        if ($currentStep === null) {
            return false;
        }

        return true;
    }
}

final readonly class RebuildSagaState
{
    public function rebuild(
        string $sagaId,
        array  $events
    ) : array
    {
        $data             = [];
        $completedSteps   = [];
        $stepResults      = [];
        $currentStepIndex = 0;
        $currentStepName  = null;

        foreach ($events as $event) {
            $type = $event['type'] ?? '';

            switch ($type) {
                case 'saga_started':
                    $data             = $event['payload'] ?? [];
                    $currentStepIndex = 0;
                    break;

                case 'step_completed':
                    $stepName               = $event['step_name'] ?? '';
                    $completedSteps[]       = $stepName;
                    $stepResults[$stepName] = $event['payload'] ?? [];
                    $currentStepName        = $stepName;
                    $currentStepIndex++;
                    break;

                case 'step_failed':
                    break 2;

                case 'saga_completed':
                case 'saga_compensated':
                    break 2;
            }
        }

        return [
            'data'               => $data,
            'completed_steps'    => $completedSteps,
            'step_results'       => $stepResults,
            'current_step_index' => $currentStepIndex,
            'current_step_name'  => $currentStepName,
        ];
    }
}

final readonly class ContinueSagaAfterFailure
{
    public function continue(
        SagaInstance $instance,
        array        $definition
    ) : array
    {
        if ($instance->status !== SagaInstanceStatus::FAILED) {
            throw new SagaRecoveryFailure(message: 'Saga is not in failed state.');
        }

        $definitionSteps = $definition['steps'] ?? [];
        $stepOrder       = $definition['stepOrder'] ?? [];

        $currentIndex = $instance->currentStepIndex;
        if ($currentIndex >= count($stepOrder)) {
            throw new SagaRecoveryFailure(message: 'No more steps to retry.');
        }

        $nextStepName = $stepOrder[$currentIndex] ?? null;
        if ($nextStepName === null) {
            throw new SagaRecoveryFailure(message: 'Could not determine next step.');
        }

        $nextStep = $definitionSteps[$nextStepName] ?? null;
        if ($nextStep === null) {
            throw new SagaRecoveryFailure(message: sprintf('Next step %s not found.', $nextStepName));
        }

        return [
            'next_step' => $nextStep,
            'retry'     => true,
        ];
    }
}

final readonly class MarkSagaAsUnrecoverable
{
    public function __construct(private object $store, private object $inspect) {}

    public function mark(
        SagaInstance $instance,
        string       $reason
    ) : void
    {
        $this->inspect->record(
            SagaRuntimeEvent::create(
                sagaId  : $instance->id,
                sagaName: $instance->definitionName,
                type    : 'saga_unrecoverable',
                payload : ['reason' => $reason]
            )
        );
    }
}

class SagaRecoveryFailure extends RuntimeException
{
    public function __construct(string $message = 'Saga recovery failed.')
    {
        parent::__construct(message: $message);
    }
}