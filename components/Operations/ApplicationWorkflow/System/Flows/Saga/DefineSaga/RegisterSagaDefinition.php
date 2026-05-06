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

    public function __construct(private readonly ValidateSagaDefinition $validateSagaDefinition = new ValidateSagaDefinition())
    {
    }

    public function register(SagaDefinition $sagaDefinition): SagaDefinition
    {
        $this->validateSagaDefinition->validate();
        $this->definitions[$sagaDefinition->name] = $sagaDefinition;

        return $sagaDefinition;
    }

    public function read(string $name): ?SagaDefinition
    {
        return $this->definitions[$name] ?? null;
    }
}
