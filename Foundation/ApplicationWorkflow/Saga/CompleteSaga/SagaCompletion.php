<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\CompleteSaga;

/**
 * SagaCompletion - terminal completion evidence for a saga instance.
 */
final readonly class SagaCompletion
{
    public function __construct(public string $instanceId, public string $status = 'completed') {}
}
