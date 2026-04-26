<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StartSaga;

use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use Avax\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent;
use Random\RandomException;
use RuntimeException;
use Throwable;

final readonly class CreateSagaInstance
{
    public function create(
        string         $id,
        SagaDefinition $definition,
        array          $initialData,
        array          $options = []
    ) : SagaInstance
    {
        return SagaInstance::create(
            id            : $id,
            definitionName: $definition->name,
            type          : $definition->type,
            initialData   : $initialData,
            options       : $options
        );
    }

    public function createFromCommand(
        SagaStartCommand $command,
        SagaDefinition   $definition
    ) : SagaInstance
    {
        return $this->create(
            id         : $command->sagaId ?? $this->generateId(),
            definition : $definition,
            initialData: $command->initialData,
            options    : [
                             'correlation_id'  => $command->correlationId,
                             'tenant_id'       => $command->tenantId,
                             'idempotency_key' => $command->idempotencyKey,
                             'timeout_seconds' => $definition->timeoutSeconds,
                         ]
        );
    }

    /**
     * @throws RandomException
     */
    private function generateId() : string
    {
        return sprintf('saga_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }
}

final readonly class CreateSagaCorrelationId
{
    /**
     * @throws RandomException
     */
    public function create(string|null $prefix = null, string|null $suffix = null) : string
    {
        $parts = array_filter([
                                  $prefix,
                                  date('YmdHis'),
                                  bin2hex(random_bytes(4)),
                                  $suffix,
                              ]);

        return implode('_', $parts);
    }

    public function fromTenant(string $tenantId) : string
    {
        return $this->create(prefix: $tenantId);
    }

    public function fromCommand(SagaStartCommand $command) : string
    {
        return $this->create(
            prefix: $command->definitionName,
            suffix: $command->tenantId
        );
    }
}

final readonly class RecordSagaStarted
{
    public function __construct(private object $store, private object $inspect) {}

    public function record(SagaInstance $instance) : void
    {
        $this->store->set("saga_{$instance->id}", $instance->toArray());

        $this->inspect->record(
            SagaRuntimeEvent::started(
                sagaId  : $instance->id,
                sagaName: $instance->definitionName
            )
        );
    }
}

final readonly class ScheduleFirstSagaStep
{
    public function schedule(SagaInstance $instance, SagaDefinition $definition) : SagaInstance
    {
        $firstStep = $definition->getFirstStep();

        if ($firstStep === null) {
            throw new SagaStartFailure(
                message: sprintf('Saga %s has no steps to execute.', $definition->name)
            );
        }

        return $instance->start(firstStepName: $firstStep->name);
    }
}

final class SagaStartFailure extends RuntimeException
{
    public function __construct(string $message = 'Failed to start saga.', Throwable|null $previous = null)
    {
        parent::__construct(message: $message, previous: $previous);
    }
}