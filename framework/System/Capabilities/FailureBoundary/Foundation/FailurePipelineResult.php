<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

/**
 * FailurePipelineResult — Result of a failure pipeline execution.
 */
final readonly class FailurePipelineResult
{
    public function __construct(
        public FailureDecision $decision,
        public mixed $value = null,
        public bool $reported = false,
        public bool $cleanupExecuted = false,
        public int $retryAttempts = 0,
    ) {
    }

    public static function retried(mixed $value, int $attempts): self
    {
        return new self(
            decision: FailureDecision::Retry,
            value: $value,
            retryAttempts: $attempts,
        );
    }

    public static function fallback(mixed $value): self
    {
        return new self(decision: FailureDecision::Fallback, value: $value);
    }

    public static function mapped(mixed $value): self
    {
        return new self(decision: FailureDecision::MapToResult, value: $value);
    }

    public static function deadLettered(): self
    {
        return new self(decision: FailureDecision::DeadLetter);
    }

    public static function rethrow(): self
    {
        return new self(decision: FailureDecision::Rethrow);
    }

    public static function reported(): self
    {
        return new self(decision: FailureDecision::ReportOnly);
    }
}
