<?php

declare(strict_types=1);

namespace Avax\Components\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

/**
 * SagaStepExecutionPolicy - visible retry and idempotency expectation for a single saga step attempt.
 */
final readonly class SagaStepExecutionPolicy
{
    public function __construct(
        public bool $idempotent = true,
        public int  $maxAttempts = 1
    ) {}
}
