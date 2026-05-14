<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;

/**
 * ChooseNextSagaStep - returns the next step name declared by the current step definition.
 */
final readonly class ChooseNextSagaStep
{
    public function choose(SagaStepDefinition $sagaStepDefinition) : string|null
    {
        return $sagaStepDefinition->nextStepName;
    }
}
