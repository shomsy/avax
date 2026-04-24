<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\ConfigureSagaRuntime;

use Closure;

/**
 * RegisterSagaStepRunner - registers the saga step runner dependency.
 */
final readonly class RegisterSagaStepRunner
{
    public function register(Closure $stepRunner, SagaRuntimeConfig|null $config = null) : SagaRuntimeConfig
    {
        return ($config ?? new SagaRuntimeConfig())->withStepRunner(stepRunner: $stepRunner);
    }

    public function describeResponsibility() : string
    {
        return 'registers the saga step runner dependency.';
    }
}
