<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency;

final readonly class DetectDuplicateSagaCommand
{
    public function __construct(private ProtectSagaIdempotency $protectSagaIdempotency) {}

    public function detect(string $key): ?SagaCommandResult
    {
        return $this->protectSagaIdempotency->check(key: $key);
    }

    public function exists(string $key): bool
    {
        return $this->protectSagaIdempotency->exists(key: $key);
    }
}

final readonly class RecordSagaCommandKey
{
    public function __construct(private ProtectSagaIdempotency $protectSagaIdempotency) {}

    public function recordSuccess(string $key, string $sagaId, array $output = []): void
    {
        $this->protectSagaIdempotency->record(
            key   : $key,
            result: SagaCommandResult::success(sagaId: $sagaId, output: $output),
        );
    }

    public function record(string $key, SagaCommandResult $sagaCommandResult) : void
    {
        $this->protectSagaIdempotency->record(key: $key, result: $sagaCommandResult);
    }

    public function recordFailure(string $key, string $sagaId, string $error): void
    {
        $this->protectSagaIdempotency->record(
            key   : $key,
            result: SagaCommandResult::failure(sagaId: $sagaId, error: $error),
        );
    }
}

final readonly class ReadPreviousSagaCommandResult
{
    public function __construct(private ProtectSagaIdempotency $protectSagaIdempotency) {}

    public function maybeReplay(string $key): ?array
    {
        $result = $this->read(key: $key);

        return $result?->output ?? null;
    }

    public function read(string $key): ?SagaCommandResult
    {
        return $this->protectSagaIdempotency->check(key: $key);
    }
}
