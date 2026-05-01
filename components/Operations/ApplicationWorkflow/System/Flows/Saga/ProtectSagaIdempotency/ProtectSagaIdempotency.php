<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Stringable;

final readonly class ProtectSagaIdempotency
{
    public function __construct(private array $commandKeys = [])
    {
    }

    public static function inMemory(): self
    {
        return new self(commandKeys: []);
    }

    public function record(string $key, SagaCommandResult $sagaCommandResult) : void
    {
        $this->commandKeys[$key] = $sagaCommandResult;
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

final readonly class SagaCommandKey implements Stringable
{
    private function __construct(public string $value, public string $aggregateType, public string $aggregateId, public string $action, public ?string $tenantId) {
    }

    public static function create(
        string $aggregateType,
        string $aggregateId,
        string $action,
        ?string $tenantId = null,
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
    private function __construct(public string $sagaId, public bool $success, public array $output, public ?string $error, public DateTimeImmutable $occurredAt) {
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
    public function __construct(
        string $message = 'Duplicate saga command detected.',
        public ?string            $sagaId = null,
        public ?SagaCommandResult $previousResult = null,
    ) {
        parent::__construct(message: $message);
    }
}
