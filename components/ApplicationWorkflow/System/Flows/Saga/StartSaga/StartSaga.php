<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\StartSaga;

use Avax\Components\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\InspectSaga\InspectSaga;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency\ProtectSagaIdempotency;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency\SagaCommandResult;
use Avax\Components\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use InvalidArgumentException;
use Random\RandomException;

final readonly class StartSaga
{
    public function __construct(
        private StoreSagaState         $storeSagaState,
        private ProtectSagaIdempotency $protectSagaIdempotency,
        private InspectSaga            $inspectSaga
    ) {}

    public function startWithIdempotency(
        SagaDefinition   $definition,
        SagaStartCommand $command
    ) : SagaInstance
    {
        if ($command->idempotencyKey !== null) {
            $existing = $this->protectSagaIdempotency->check(key: $command->idempotencyKey);
            if ($existing !== null) {
                return $existing;
            }
        }

        $instance = $this->start(definition: $definition, command: $command);

        if ($command->idempotencyKey !== null) {
            $this->protectSagaIdempotency->record(
                key   : $command->idempotencyKey,
                result: SagaCommandResult::success(sagaId: $instance->id, output: $instance->toArray())
            );
        }

        return $instance;
    }

    public function start(
        SagaDefinition   $definition,
        SagaStartCommand $command,
        callable         $correlationIdGenerator = null
    ) : SagaInstance
    {
        if (! $definition->isValid()) {
            throw new SagaStartFailure(
                message: sprintf('Saga definition %s is invalid: %s', $definition->name, implode(', ', $definition->getValidationErrors()))
            );
        }

        $firstStep = $definition->getFirstStep();
        if ($firstStep === null) {
            throw new SagaStartFailure(message: 'Saga has no steps to execute.');
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

        $instance = $instance->start(firstStepName: $firstStep->name);

        $this->storeSagaState->save(instance: $instance);

        $this->inspectSaga->record(
            event: SagaRuntimeEvent::started(sagaId: $id, sagaName: $definition->name)
        );

        return $instance;
    }

    /**
     * @throws RandomException
     */
    private function createCorrelationId() : string
    {
        return sprintf('saga_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }
}

final readonly class SagaStartCommand
{
    public string|null $sagaId;
    public string      $definitionName;
    public array       $initialData;
    public string|null $correlationId;
    public string|null $tenantId;
    public string|null $idempotencyKey;

    private function __construct(
        string|null $sagaId,
        string      $definitionName,
        array       $initialData,
        string|null $correlationId,
        string|null $tenantId,
        string|null $idempotencyKey
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
        string     $definitionName,
        array|null $initialData = null,
        array      $options = []
    ) : self
    {
        $initialData ??= [];
        if (empty(trim($definitionName))) {
            throw new InvalidArgumentException(message: 'Definition name cannot be empty.');
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
    public string      $value;
    public string|null $prefix;
    public string|null $suffix;

    private function __construct(string $value, string|null $prefix = null, string|null $suffix = null)
    {
        $this->value  = $value;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    /**
     * @throws RandomException
     */
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

        return new self(value: $value, prefix: $prefix, suffix: $suffix);
    }

    public static function fromString(string $value) : self
    {
        return new self(value: $value);
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