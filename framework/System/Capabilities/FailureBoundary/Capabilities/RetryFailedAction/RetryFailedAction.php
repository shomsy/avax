<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RetryFailedAction;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Closure;
use Throwable;

/**
 * RetryFailedAction — Executes a retry loop with configurable backoff.
 */
final readonly class RetryFailedAction
{
    public function execute(
        Throwable $failure,
        FailureContext $context,
        FailurePolicy $policy,
        Closure $originalAction,
    ): mixed {
        $maxAttempts = $policy->retryMaxAttempts ?? 3;
        $backoff = $policy->retryBackoff;
        $delayMs = $policy->retryDelayMs;
        $jitter = $policy->retryJitter;

        $lastException = $failure;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $result = $originalAction();
                return FailurePipelineResult::retried($result, $attempt);
            } catch (Throwable $e) {
                $lastException = $e;

                if ($attempt < $maxAttempts) {
                    $waitMs = $this->calculateDelay($backoff, $delayMs, $attempt, $jitter);
                    if ($waitMs > 0) {
                        usleep($waitMs * 1000);
                    }
                }
            }
        }

        // Retry exhausted — if dead letter is configured, send there
        // Otherwise, the caller will handle via the pipeline decision
        throw $lastException;
    }

    private function calculateDelay(string $backoff, int $baseDelayMs, int $attempt, bool $jitter): int
    {
        $delayMs = match ($backoff) {
            'exponential' => $baseDelayMs * (2 ** ($attempt - 1)),
            'linear' => $baseDelayMs * $attempt,
            default => $baseDelayMs,
        };

        if ($jitter && $delayMs > 0) {
            $delayMs = (int) ($delayMs * (0.5 + (mt_rand() / mt_getrandmax()) * 0.5));
        }

        return $delayMs;
    }
}
