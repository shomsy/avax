<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ConfigureSagaRuntime;

use RuntimeException;

/**
 * SagaRuntimeConfigurationFailure - reports invalid saga runtime configuration.
 */
final class SagaRuntimeConfigurationFailure extends RuntimeException
{
    public static function missingSagaStore() : self
    {
        return new self(message: 'Saga runtime requires an explicit StoreSagaState dependency before workflow state can be written.');
    }

    public static function missingSagaStepRunner() : self
    {
        return new self(message: 'Saga runtime requires an explicit step runner before workflow steps can execute.');
    }
}
