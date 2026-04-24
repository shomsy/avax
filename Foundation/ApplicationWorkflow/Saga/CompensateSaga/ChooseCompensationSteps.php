<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\CompensateSaga;

use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;
use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstance;
use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstanceStatus;
use InvalidArgumentException;

final readonly class ChooseCompensationSteps
{
    public function describeResponsibility() : string
    {
        return 'chooses compensation steps in reverse successful side-effect order.';
    }

    /**
     * @return array<int, SagaStepDefinition>
     */
    public function choose(SagaInstance $saga, CompensationPlan $plan) : array
    {
        $this->validateSagaInstance($saga);

        $completedSteps = $saga->completedSteps;

        if (empty($completedSteps)) {
            return [];
        }

        $definitions = $plan->getStepDefinitions();

        return $this->selectCompensationSteps($completedSteps, $definitions);
    }

    private function validateSagaInstance(SagaInstance $saga) : void
    {
        if (empty($saga->id)) {
            throw new InvalidArgumentException('Saga instance ID cannot be empty.');
        }

        if ($saga->status->value === SagaInstanceStatus::PENDING->value) {
            throw new InvalidArgumentException(
                sprintf('Cannot choose compensation steps for pending saga %s.', $saga->id)
            );
        }
    }

    /**
     * @param array<int, string>                $completedSteps
     * @param array<string, SagaStepDefinition> $definitions
     *
     * @return array<int, SagaStepDefinition>
     */
    private function selectCompensationSteps(array $completedSteps, array $definitions) : array
    {
        $compensationSteps = [];

        $reversedSteps = array_reverse($completedSteps, preserve_keys: true);

        foreach ($reversedSteps as $index => $stepName) {
            $stepDef = $definitions[$stepName] ?? null;

            if (! $stepDef instanceof SagaStepDefinition) {
                continue;
            }

            if (! $this->stepHasCompensation($stepDef)) {
                continue;
            }

            $compensationSteps[] = $stepDef;
        }

        return $compensationSteps;
    }

    private function stepHasCompensation(SagaStepDefinition $stepDef) : bool
    {
        return $stepDef->hasCompensation();
    }
}