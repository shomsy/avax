<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use Random\RandomException;
use RuntimeException;
use Throwable;

final readonly class CreateSagaInstance
{
    public function createFromCommand(
        SagaStartCommand $sagaStartCommand,
        SagaDefinition   $sagaDefinition,
    ) : SagaInstance
    {
        return $this->create(
            id         : $sagaStartCommand->sagaId ?? $this->generateId(),
            initialData: $sagaStartCommand->initialData,
            options    : [
                             'correlation_id'  => $sagaStartCommand->correlationId,
                             'tenant_id'       => $sagaStartCommand->tenantId,
                             'idempotency_key' => $sagaStartCommand->idempotencyKey,
                             'timeout_seconds' => $sagaDefinition->timeoutSeconds,
                         ],
            definition : $sagaDefinition,
        );
    }

    public function create(
        string         $id,
        SagaDefinition $sagaDefinition,
        array          $initialData,
        array          $options = [],
    ) : SagaInstance
    {
        return SagaInstance::create(
            id            : $id,
            definitionName: $sagaDefinition->name,
            type          : $sagaDefinition->type,
            initialData   : $initialData,
            options       : $options,
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
    public function fromTenant(string $tenantId) : string
    {
        return $this->create(prefix: $tenantId);
    }

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

    public function fromCommand(SagaStartCommand $sagaStartCommand) : string
    {
        return $this->create(
            prefix: $sagaStartCommand->definitionName,
        );
    }
}

final readonly class RecordSagaStarted
{
    public function __construct(private object $store, private object $inspect) {}

    public function record(SagaInstance $sagaInstance) : void
    {
        $this->store->set('saga_' . $sagaInstance->id, $sagaInstance->toArray());

        $this->inspect->record(
            SagaRuntimeEvent::started(
                sagaId  : $sagaInstance->id,
                sagaName: $sagaInstance->definitionName,
            ),
        );
    }
}

final readonly class ScheduleFirstSagaStep
{
    /**
 * @throws SagaStartFailure
 */
public function schedule(SagaInstance $sagaInstance, SagaDefinition $sagaDefinition) : SagaInstance
    {
        $firstStep = $sagaDefinition->getFirstStep();

        if (! $firstStep instanceof SagaStepDefinition) {
            throw new SagaStartFailure(
                message: sprintf('Saga %s has no steps to execute.', $sagaDefinition->name),
            );
        }

        return $sagaInstance->start(firstStepName: $firstStep->name);
    }
}

final class SagaStartFailure extends RuntimeException
{
    public function __construct(string $message = 'Failed to start saga.', Throwable|null $previous = null)
    {
        parent::__construct(message: $message, previous: $previous);
    }
}
