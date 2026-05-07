<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\ProtectSagaIdempotency;

/**
 * SagaCommandKey - stable idempotency key for start, step, and compensation commands.
 */
final readonly class SagaCommandKey
{
    public function __construct(public string $value) {}
}
