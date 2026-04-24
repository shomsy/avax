<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\StartSaga;

/**
 * SagaCorrelationId - stable identifier used to connect saga state, events, retries, and diagnostics.
 */
final readonly class SagaCorrelationId
{
    public function __construct(public string $value) {}
}
