<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\ProtectSagaIdempotency;

/**
 * SagaCommandResult - replayable result stored for an idempotent saga command.
 */
final readonly class SagaCommandResult
{
    public function __construct(public mixed $result) {}
}
