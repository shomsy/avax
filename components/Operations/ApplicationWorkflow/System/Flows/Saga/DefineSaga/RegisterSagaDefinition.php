<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

/**
 * RegisterSagaDefinition - records validated saga definitions by name.
 */
final class RegisterSagaDefinition
{
    /** @var array<string, SagaDefinition> */
    private array $definitions = [];

    public function __construct(private readonly ValidateSagaDefinition $validateSagaDefinition) {}

    /**
     * @throws InvalidSagaDefinitionException when saga definition validation fails
     */
    public function register(SagaDefinition $sagaDefinition) : SagaDefinition
    {
        $result = $this->validateSagaDefinition->validate(sagaDefinition: $sagaDefinition);

        if (! $result->isValid()) {
            throw new InvalidSagaDefinitionException(errors: $result->errors);
        }

        $this->definitions[$sagaDefinition->name] = $sagaDefinition;

        return $sagaDefinition;
    }

    public function read(string $name) : ?SagaDefinition
    {
        return $this->definitions[$name] ?? null;
    }
}
