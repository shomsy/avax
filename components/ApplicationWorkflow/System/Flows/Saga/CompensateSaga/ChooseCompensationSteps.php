<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\CompensateSaga;

use Avax\Components\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstance;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\StartSaga\SagaInstanceStatus;
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
    public function choose(SagaInstance $saga, SagaDefinition $definition) : array
    {
        $this->validateSagaInstance(saga: $saga);

        $completedSteps = $saga->completedSteps;
        if (empty($completedSteps)) {
            return [];
        }

        $compensationSteps = [];

        // Reverse the order of completed steps for compensation (LIFO)
        $reversedCompletedSteps = array_reverse($completedSteps);

        foreach ($reversedCompletedSteps as $stepName) {
            $stepDef = $definition->getStep(name: $stepName);

            if ($stepDef !== null && $stepDef->hasCompensation()) {
                $compensationSteps[] = $stepDef;
            }
        }

        return $compensationSteps;
    }

    private function validateSagaInstance(SagaInstance $saga) : void
    {
        if (empty($saga->id)) {
            throw new InvalidArgumentException(message: 'Saga instance ID cannot be empty.');
        }

        if ($saga->status === SagaInstanceStatus::PENDING) {
            throw new InvalidArgumentException(
                message: sprintf('Cannot choose compensation steps for pending saga %s.', $saga->id)
            );
        }
    }
}