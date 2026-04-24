<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\ProtectSagaIdempotency;

final readonly class DetectDuplicateSagaCommand
{
    public function __construct(private ProtectSagaIdempotency $idempotency) {}

    public function detect(string $key) : ?SagaCommandResult
    {
        return $this->idempotency->check($key);
    }

    public function exists(string $key) : bool
    {
        return $this->idempotency->exists($key);
    }
}

final readonly class RecordSagaCommandKey
{
    public function __construct(private ProtectSagaIdempotency $idempotency) {}

    public function record(string $key, SagaCommandResult $result) : void
    {
        $this->idempotency->record($key, $result);
    }

    public function recordSuccess(string $key, string $sagaId, array $output = []) : void
    {
        $this->idempotency->record(
            $key,
            SagaCommandResult::success($sagaId, $output)
        );
    }

    public function recordFailure(string $key, string $sagaId, string $error) : void
    {
        $this->idempotency->record(
            $key,
            SagaCommandResult::failure($sagaId, $error)
        );
    }
}

final readonly class ReadPreviousSagaCommandResult
{
    public function __construct(private ProtectSagaIdempotency $idempotency) {}

    public function read(string $key) : ?SagaCommandResult
    {
        return $this->idempotency->check($key);
    }

    public function maybeReplay(string $key) : ?array
    {
        $result = $this->read($key);

        return $result?->output ?? null;
    }
}