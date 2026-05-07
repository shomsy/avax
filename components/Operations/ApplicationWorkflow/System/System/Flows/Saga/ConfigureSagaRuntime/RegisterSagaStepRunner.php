<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Flows\Saga\ConfigureSagaRuntime;

use RuntimeException;

final readonly class RegisterSagaStepRunner
{
    public function register() : object
    {
        return new class () {
            public function run(array $stepDefinition, array $sagaData) : mixed
            {
                throw new RuntimeException(message: 'Step runner not configured.');
            }

            public function compensate(array $compensationDefinition, array $previousResult) : mixed
            {
                throw new RuntimeException(message: 'Compensation runner not configured.');
            }
        };
    }
}
