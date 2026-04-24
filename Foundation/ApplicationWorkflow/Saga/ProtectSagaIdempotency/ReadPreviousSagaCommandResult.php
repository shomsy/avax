<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\ProtectSagaIdempotency;

/**
 * ReadPreviousSagaCommandResult - returns the replayable result for a duplicate command key.
 */
final readonly class ReadPreviousSagaCommandResult
{
    /**
     * @param array<string, SagaCommandResult> $results
     */
    public function read(array $results, SagaCommandKey $key) : SagaCommandResult|null
    {
        return $results[$key->value] ?? null;
    }
}
