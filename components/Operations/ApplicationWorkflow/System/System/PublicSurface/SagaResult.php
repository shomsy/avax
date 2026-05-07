<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\PublicSurface;

/**
 * Result of a saga execution.
 */
final readonly class SagaResult
{
    public function __construct(
        public bool    $success,
        public string  $sagaId,
        public array   $data,
        public array   $completedSteps,
        public array   $stepResults,
        public ?string $failureReason = null,
    ) {}

    public function isSuccessful() : bool
    {
        return $this->success;
    }

    public function getFailureReason() : ?string
    {
        return $this->failureReason;
    }

    public function getStepResult(string $stepName) : mixed
    {
        return $this->stepResults[$stepName] ?? null;
    }
}
