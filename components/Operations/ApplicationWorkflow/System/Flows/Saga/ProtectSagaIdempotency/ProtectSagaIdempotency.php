<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;

final readonly class ProtectSagaIdempotency
{
    private array $commandKeys;

    public function __construct(array $commandKeys = [])
    {
        $this->commandKeys = $commandKeys;
    }

    public static function inMemory(): self
    {
        return new self(commandKeys: []);
    }

    public function record(string $key, SagaCommandResult $result): void
    {
        $this->commandKeys[$key] = $result;
    }

    public function check(string $key): ?SagaCommandResult
    {
        return $this->commandKeys[$key] ?? null;
    }

    public function exists(string $key): bool
    {
        return isset($this->commandKeys[$key]);
    }

    public function generateKey(string $aggregateType, string $aggregateId, string $action): string
    {
        return sprintf('%s:%s:%s', $aggregateType, $aggregateId, $action);
    }
}

final readonly class SagaCommandKey
{
    public string $value;

    public string $aggregateType;

    public string $aggregateId;

    public string $action;

    public ?string $tenantId;

    private function __construct(
        string $value,
        string $aggregateType,
        string $aggregateId,
        string $action,
        ?string $tenantId,
    ) {
        $this->value       = $value;
        $this->aggregateType = $aggregateType;
        $this->aggregateId = $aggregateId;
        $this->action      = $action;
        $this->tenantId    = $tenantId;
    }

    public static function create(
        string $aggregateType,
        string $aggregateId,
        string $action,
        string $tenantId = null,
    ): self {
        $parts = array_filter([$aggregateType, $aggregateId, $action, $tenantId]);
        $value = implode(':', $parts);

        return new self(
            value        : $value,
            aggregateType: $aggregateType,
            aggregateId  : $aggregateId,
            action       : $action,
            tenantId     : $tenantId,
        );
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

final readonly class SagaCommandResult
{
    public string $sagaId;

    public bool $success;

    public array $output;

    public ?string $error;

    public DateTimeImmutable $occurredAt;

    private function __construct(
        string $sagaId,
        bool $success,
        array $output,
        ?string $error,
        DateTimeImmutable $occurredAt,
    ) {
        $this->sagaId  = $sagaId;
        $this->success = $success;
        $this->output  = $output;
        $this->error   = $error;
        $this->occurredAt = $occurredAt;
    }

    public static function success(string $sagaId, array $output = []): self
    {
        return new self(
            sagaId    : $sagaId,
            success   : true,
            output    : $output,
            error     : null,
            occurredAt: new DateTimeImmutable(),
        );
    }

    public static function failure(string $sagaId, string $error): self
    {
        return new self(
            sagaId    : $sagaId,
            success   : false,
            output    : [],
            error     : $error,
            occurredAt: new DateTimeImmutable(),
        );
    }

    public function toArray(): array
    {
        return [
            'saga_id' => $this->sagaId,
            'success' => $this->success,
            'output'  => $this->output,
            'error'   => $this->error,
            'occurred_at' => $this->occurredAt->format(format: DateTimeInterface::ISO8601),
        ];
    }
}

final class DuplicateSagaCommand extends Exception
{
    public ?string $sagaId;

    public ?SagaCommandResult $previousResult;

    public function __construct(
        string $message = 'Duplicate saga command detected.',
        string            $sagaId = null,
        SagaCommandResult $previousResult = null,
    ) {
        parent::__construct(message: $message);
        $this->sagaId = $sagaId;
        $this->previousResult = $previousResult;
    }
}
