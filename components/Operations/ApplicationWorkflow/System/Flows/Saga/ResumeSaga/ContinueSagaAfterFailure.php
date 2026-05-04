<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ResumeSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstanceStatus;

final readonly class ContinueSagaAfterFailure
{
    public function continue(
        SagaInstance $sagaInstance,
        array $definition,
    ): array {
        if ($sagaInstance->status !== SagaInstanceStatus::FAILED) {
            throw new SagaRecoveryFailure(message: 'Saga is not in failed state.');
        }

        $definitionSteps = $definition['steps'] ?? [];
        $stepOrder       = $definition['stepOrder'] ?? [];

        $currentIndex = $sagaInstance->currentStepIndex;
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
            'retry' => true,
        ];
    }
}
