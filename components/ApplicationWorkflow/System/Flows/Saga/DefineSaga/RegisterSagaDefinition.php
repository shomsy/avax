<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\DefineSaga;

/**
 * RegisterSagaDefinition - records validated saga definitions by name.
 */
final class RegisterSagaDefinition
{
    /** @var array<string, SagaDefinition> */
    private array $definitions = [];

    public function __construct(private readonly ValidateSagaDefinition $validateSagaDefinition = new ValidateSagaDefinition()) {}

    public function register(SagaDefinition $definition) : SagaDefinition
    {
        $this->validateSagaDefinition->validate(definition: $definition);
        $this->definitions[$definition->name] = $definition;

        return $definition;
    }

    public function read(string $name) : SagaDefinition|null
    {
        return $this->definitions[$name] ?? null;
    }
}
