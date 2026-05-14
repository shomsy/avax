<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

/**
 * DefineSaga - public entry point for registering and validating saga definitions before runtime.
 */
final readonly class DefineSaga
{
    public function __construct(
        private ValidateSagaDefinition $validateSagaDefinition,
        private RegisterSagaDefinition $registerSagaDefinition,
    ) {}

    /**
     * @throws InvalidSagaDefinitionException when saga definition validation fails
     */
    public function validate(SagaDefinition $sagaDefinition) : SagaValidationResult
    {
        $result = $this->validateSagaDefinition->validate(sagaDefinition: $sagaDefinition);

        if (! $result->isValid()) {
            throw new InvalidSagaDefinitionException(errors: $result->errors);
        }

        return $result;
    }

    /**
     * @throws InvalidSagaDefinitionException when saga definition validation fails
     */
    public function register(SagaDefinition $sagaDefinition) : SagaDefinition
    {
        $this->validate(sagaDefinition: $sagaDefinition);

        return $this->registerSagaDefinition->register(definition: $sagaDefinition);
    }
}
