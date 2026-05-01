<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

/**
 * ValidateSagaRuntimeConfig - validates saga runtime dependencies before execution.
 */
final readonly class ValidateSagaRuntimeConfig
{
    public function validate(SagaRuntimeConfig $sagaRuntimeConfig) : void
    {
        if ($sagaRuntimeConfig->store === null) {
            throw SagaRuntimeConfigurationFailure::missingSagaStore();
        }

        if ($sagaRuntimeConfig->stepRunner === null) {
            throw SagaRuntimeConfigurationFailure::missingSagaStepRunner();
        }
    }

    public function describeResponsibility(): string
    {
        return 'validates saga runtime dependencies before execution.';
    }
}
