<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaExecutor;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Saga\SagaStep;
use Throwable;

/**
 * Executes a saga definition with compensation on failure.
 *
 * Runs each step in order. If any step fails, runs compensation
 * for all completed steps in reverse order.
 */
final class SagaExecutor
{
    /**
     * Execute the saga definition.
     *
     * @return list<string> Names of successfully completed steps
     *
     * @throws Throwable The original exception from the failed step,
     *                   after all compensations have run.
     */
    public function execute(SagaDefinition $sagaDefinition): array
    {
        $completed = [];

        try {
            foreach ($sagaDefinition->getSteps() as $step) {
                ($step->action)();
                $completed[] = $step->name;
            }
        } catch (Throwable $throwable) {
            foreach (array_reverse($completed) as $completedStepName) {
                $step = $this->findStepByName($sagaDefinition, $completedStepName);
                if ($step !== null && $step->compensation !== null) {
                    ($step->compensation)();
                }
            }

            throw $throwable;
        }

        return $completed;
    }

    private function findStepByName(SagaDefinition $sagaDefinition, string $name): ?SagaStep
    {
        foreach ($sagaDefinition->getSteps() as $step) {
            if ($step->name === $name) {
                return $step;
            }
        }

        return null;
    }
}
