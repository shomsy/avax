<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

use RuntimeException;

final readonly class RegisterSagaStepRunner
{
    public function register() : object
    {
        return new class () {
            /**
             * @param array<string, mixed> $stepDefinition
             * @param array<string, mixed> $sagaData
             */
            public function run(array $stepDefinition, array $sagaData) : mixed
            {
                throw new RuntimeException(message: 'Step runner not configured.');
            }

            /**
             * @param array<string, mixed> $compensationDefinition
             * @param array<string, mixed> $previousResult
             */
            public function compensate(array $compensationDefinition, array $previousResult) : mixed
            {
                throw new RuntimeException(message: 'Compensation runner not configured.');
            }
        };
    }
}
