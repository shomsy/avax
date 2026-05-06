<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

/**
 * DefineSaga - public entry point for registering and validating saga definitions before runtime.
 */
final readonly class DefineSaga
{
    public function __construct(
        private ValidateSagaDefinition $validateSagaDefinition = new ValidateSagaDefinition(),
        private RegisterSagaDefinition $registerSagaDefinition = new RegisterSagaDefinition(),
    ) {
    }

    public function validate(): void
    {
        $this->validateSagaDefinition->validate();
    }

    public function register(SagaDefinition $sagaDefinition): SagaDefinition
    {
        return $this->registerSagaDefinition->register(definition: $sagaDefinition);
    }
}
