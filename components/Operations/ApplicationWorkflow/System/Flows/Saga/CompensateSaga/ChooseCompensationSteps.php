<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompensateSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstanceStatus;
use InvalidArgumentException;

/**
 * Strategy for choosing which steps to compensate and in what order.
 * Standard SAGA pattern requires compensating successful steps in reverse order of completion.
 */
final readonly class ChooseCompensationSteps
{
    /**
     * Chooses compensation steps based on saga completion history and definition.
     *
     * @return array<int, SagaStepDefinition>
     */
    public function choose(SagaInstance $sagaInstance, SagaDefinition $sagaDefinition): array
    {
        $this->validateSagaInstance(saga: $sagaInstance);

        $completedSteps = $sagaInstance->completedSteps;
        if ($completedSteps === []) {
            return [];
        }

        $compensationSteps = [];

        // Reverse the order of completed steps for compensation (LIFO)
        $reversedCompletedSteps = array_reverse($completedSteps);

        foreach ($reversedCompletedSteps as $reversedCompletedStep) {
            $stepDef = $sagaDefinition->getStep(name: $reversedCompletedStep);

            if ($stepDef instanceof SagaStepDefinition && $stepDef->hasCompensation()) {
                $compensationSteps[] = $stepDef;
            }
        }

        return $compensationSteps;
    }

    private function validateSagaInstance(SagaInstance $sagaInstance): void
    {
        if ($sagaInstance->id === '' || $sagaInstance->id === '0') {
            throw new InvalidArgumentException(message: 'Saga instance ID cannot be empty.');
        }

        if ($sagaInstance->status === SagaInstanceStatus::PENDING) {
            throw new InvalidArgumentException(
                message: sprintf('Cannot choose compensation steps for pending saga %s.', $sagaInstance->id),
            );
        }
    }
}
