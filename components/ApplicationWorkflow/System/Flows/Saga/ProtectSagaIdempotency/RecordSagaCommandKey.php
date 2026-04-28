<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency;

final readonly class DetectDuplicateSagaCommand
{
    public function __construct(private ProtectSagaIdempotency $idempotency) {}

    public function detect(string $key) : SagaCommandResult|null
    {
        return $this->idempotency->check(key: $key);
    }

    public function exists(string $key) : bool
    {
        return $this->idempotency->exists(key: $key);
    }
}

final readonly class RecordSagaCommandKey
{
    public function __construct(private ProtectSagaIdempotency $idempotency) {}

    public function recordSuccess(string $key, string $sagaId, array $output = []) : void
    {
        $this->idempotency->record(
            key   : $key,
            result: SagaCommandResult::success(sagaId: $sagaId, output: $output)
        );
    }

    public function record(string $key, SagaCommandResult $result) : void
    {
        $this->idempotency->record(key: $key, result: $result);
    }

    public function recordFailure(string $key, string $sagaId, string $error) : void
    {
        $this->idempotency->record(
            key   : $key,
            result: SagaCommandResult::failure(sagaId: $sagaId, error: $error)
        );
    }
}

final readonly class ReadPreviousSagaCommandResult
{
    public function __construct(private ProtectSagaIdempotency $idempotency) {}

    public function maybeReplay(string $key) : array|null
    {
        $result = $this->read(key: $key);

        return $result?->output ?? null;
    }

    public function read(string $key) : SagaCommandResult|null
    {
        return $this->idempotency->check(key: $key);
    }
}