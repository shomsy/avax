<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\RunSagaStep;

use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;

/**
 * ChooseNextSagaStep - returns the next step name declared by the current step definition.
 */
final readonly class ChooseNextSagaStep
{
    public function choose(SagaStepDefinition $step) : string|null
    {
        return $step->nextStepName;
    }
}
