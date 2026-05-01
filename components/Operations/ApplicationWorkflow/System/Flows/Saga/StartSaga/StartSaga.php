<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StartSaga;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\InspectSaga;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\InspectSaga\SagaRuntimeEvent;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency\ProtectSagaIdempotency;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency\SagaCommandResult;
use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState\StoreSagaState;
use InvalidArgumentException;
use Random\RandomException;
use Stringable;

final readonly class StartSaga
{
    public function __construct(
        private StoreSagaState $storeSagaState,
        private ProtectSagaIdempotency $protectSagaIdempotency,
        private InspectSaga $inspectSaga,
    ) {}

    public function startWithIdempotency(
        SagaDefinition   $sagaDefinition,
        SagaStartCommand $sagaStartCommand,
    ): SagaInstance {
        if ($sagaStartCommand->idempotencyKey !== null) {
            $existing = $this->protectSagaIdempotency->check(key: $sagaStartCommand->idempotencyKey);
            if ($existing instanceof SagaCommandResult) {
                return $existing;
            }
        }

        $sagaInstance = $this->start(definition: $sagaDefinition, command: $sagaStartCommand);

        if ($sagaStartCommand->idempotencyKey !== null) {
            $this->protectSagaIdempotency->record(
                key   : $sagaStartCommand->idempotencyKey,
                result: SagaCommandResult::success(sagaId: $sagaInstance->id, output: $sagaInstance->toArray()),
            );
        }

        return $sagaInstance;
    }

    public function start(
        SagaDefinition   $sagaDefinition,
        SagaStartCommand $sagaStartCommand,
        ?callable        $correlationIdGenerator = null,
    ): SagaInstance {
        if (! $sagaDefinition->isValid()) {
            throw new SagaStartFailure(
                message: sprintf('Saga definition %s is invalid: %s', $sagaDefinition->name, implode(', ', $sagaDefinition->getValidationErrors())),
            );
        }

        $firstStep = $sagaDefinition->getFirstStep();
        if (! $firstStep instanceof SagaStepDefinition) {
            throw new SagaStartFailure(message: 'Saga has no steps to execute.');
        }

        $id            = $sagaStartCommand->sagaId ?? ($correlationIdGenerator ?? 'default')();
        $correlationId = $sagaStartCommand->correlationId ?? $this->createCorrelationId();

        $instance = SagaInstance::create(
            id            : $id,
            definitionName: $sagaDefinition->name,
            type          : $sagaDefinition->type,
            initialData   : $sagaStartCommand->initialData,
            options       : [
                                'correlation_id' => $correlationId,
                                'tenant_id'       => $sagaStartCommand->tenantId,
                                'timeout_seconds' => $sagaDefinition->timeoutSeconds,
            ],
        );

        $instance = $instance->start(firstStepName: $firstStep->name);

        $this->storeSagaState->save(instance: $instance);

        $this->inspectSaga->record(
            event: SagaRuntimeEvent::started(sagaId: $id, sagaName: $sagaDefinition->name),
        );

        return $instance;
    }

    /**
     * @throws RandomException
     */
    private function createCorrelationId(): string
    {
        return sprintf('saga_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }
}

final readonly class SagaStartCommand
{
    private function __construct(public ?string $sagaId, public string $definitionName, public array $initialData, public ?string $correlationId, public ?string $tenantId, public ?string $idempotencyKey) {
    }

    public static function create(
        string $definitionName,
        ?array $initialData = null,
        array $options = [],
    ): self {
        $initialData ??= [];
        if (in_array(trim($definitionName), ['', '0'], true)) {
            throw new InvalidArgumentException(message: 'Definition name cannot be empty.');
        }

        return new self(
            sagaId        : $options['saga_id'] ?? null,
            definitionName: $definitionName,
            initialData   : $initialData,
            correlationId : $options['correlation_id'] ?? null,
            tenantId      : $options['tenant_id'] ?? null,
            idempotencyKey: $options['idempotency_key'] ?? null,
        );
    }

    public function withSagaId(string $id): self
    {
        return new self(
            sagaId        : $id,
            definitionName: $this->definitionName,
            initialData   : $this->initialData,
            correlationId : $this->correlationId,
            tenantId      : $this->tenantId,
            idempotencyKey: $this->idempotencyKey,
        );
    }
}

final readonly class SagaCorrelationId implements Stringable
{
    private function __construct(public string $value, public ?string $prefix = null, public ?string $suffix = null)
    {
    }

    /**
     * @throws RandomException
     */
    public static function generate(array $options = []): self
    {
        $prefix = $options['prefix'] ?? null;
        $suffix = $options['suffix'] ?? null;

        $value = sprintf(
            '%s%s%s',
            $prefix ?? '',
            date('YmdHis'),
            bin2hex(random_bytes(6)),
        );
        if ($suffix !== null) {
            $value .= '_' . $suffix;
        }

        return new self(value: $value, prefix: $prefix, suffix: $suffix);
    }

    public static function fromString(string $value): self
    {
        return new self(value: $value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
