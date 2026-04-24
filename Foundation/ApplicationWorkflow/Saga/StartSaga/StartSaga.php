<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StartSaga;

use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;
use Avax\ApplicationWorkflow\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\ApplicationWorkflow\Saga\ProtectSagaIdempotency\SagaCommandResult;
use InvalidArgumentException;

final readonly class StartSaga
{
    public function __construct(
        private \Avax\ApplicationWorkflow\Saga\StoreSagaState\StoreSagaState                 $storeSagaState,
        private \Avax\ApplicationWorkflow\Saga\ProtectSagaIdempotency\ProtectSagaIdempotency $protectSagaIdempotency,
        private \Avax\ApplicationWorkflow\Saga\InspectSaga\InspectSaga                       $inspectSaga
    ) {}

    public function start(
        SagaDefinition   $definition,
        SagaStartCommand $command,
        callable         $correlationIdGenerator = null
    ) : SagaInstance
    {
        if (! $definition->isValid()) {
            throw new SagaStartFailure(
                sprintf('Saga definition %s is invalid: %s', $definition->name, implode(', ', $definition->getValidationErrors()))
            );
        }

        $firstStep = $definition->getFirstStep();
        if ($firstStep === null) {
            throw new SagaStartFailure('Saga has no steps to execute.');
        }

        $id            = $command->sagaId ?? ($correlationIdGenerator ?? 'default')();
        $correlationId = $command->correlationId ?? $this->createCorrelationId();

        $instance = SagaInstance::create(
            id            : $id,
            definitionName: $definition->name,
            type          : $definition->type,
            initialData   : $command->initialData,
            options       : [
                                'correlation_id'  => $correlationId,
                                'tenant_id'       => $command->tenantId,
                                'timeout_seconds' => $definition->timeoutSeconds,
                            ]
        );

        $instance = $instance->start($firstStep->name);

        $this->storeSagaState->save($instance);

        $this->inspectSaga->record(
            SagaRuntimeEvent::started($id, $definition->name)
        );

        return $instance;
    }

    public function startWithIdempotency(
        SagaDefinition   $definition,
        SagaStartCommand $command
    ) : SagaInstance
    {
        if ($command->idempotencyKey !== null) {
            $existing = $this->protectSagaIdempotency->check($command->idempotencyKey);
            if ($existing !== null) {
                return $existing;
            }
        }

        $instance = $this->start($definition, $command);

        if ($command->idempotencyKey !== null) {
            $this->protectSagaIdempotency->record(
                $command->idempotencyKey,
                SagaCommandResult::success($instance->id, $instance->toArray())
            );
        }

        return $instance;
    }

    private function createCorrelationId() : string
    {
        return sprintf('saga_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }
}

final readonly class SagaStartCommand
{
    public ?string $sagaId;
    public string  $definitionName;
    public array   $initialData;
    public ?string $correlationId;
    public ?string $tenantId;
    public ?string $idempotencyKey;

    private function __construct(
        ?string $sagaId,
        string  $definitionName,
        array   $initialData,
        ?string $correlationId,
        ?string $tenantId,
        ?string $idempotencyKey
    )
    {
        $this->sagaId         = $sagaId;
        $this->definitionName = $definitionName;
        $this->initialData    = $initialData;
        $this->correlationId  = $correlationId;
        $this->tenantId       = $tenantId;
        $this->idempotencyKey = $idempotencyKey;
    }

    public static function create(
        string $definitionName,
        array  $initialData = [],
        array  $options = []
    ) : self
    {
        if (empty(trim($definitionName))) {
            throw new InvalidArgumentException('Definition name cannot be empty.');
        }

        return new self(
            sagaId        : $options['saga_id'] ?? null,
            definitionName: $definitionName,
            initialData   : $initialData,
            correlationId : $options['correlation_id'] ?? null,
            tenantId      : $options['tenant_id'] ?? null,
            idempotencyKey: $options['idempotency_key'] ?? null
        );
    }

    public function withSagaId(string $id) : self
    {
        return new self(
            sagaId        : $id,
            definitionName: $this->definitionName,
            initialData   : $this->initialData,
            correlationId : $this->correlationId,
            tenantId      : $this->tenantId,
            idempotencyKey: $this->idempotencyKey
        );
    }
}

final readonly class SagaCorrelationId
{
    public string  $value;
    public ?string $prefix;
    public ?string $suffix;

    private function __construct(string $value, ?string $prefix = null, ?string $suffix = null)
    {
        $this->value  = $value;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    public static function generate(array $options = []) : self
    {
        $prefix = $options['prefix'] ?? null;
        $suffix = $options['suffix'] ?? null;

        $value = sprintf(
            '%s%s%s',
            $prefix ?? '',
            date('YmdHis'),
            bin2hex(random_bytes(6))
        );
        if ($suffix !== null) {
            $value .= "_{$suffix}";
        }

        return new self($value, $prefix, $suffix);
    }

    public static function fromString(string $value) : self
    {
        return new self($value);
    }

    public function toString() : string
    {
        return $this->value;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}