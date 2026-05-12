<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RetryFailedAction;

use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryExecutor;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryOptions;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Closure;
use Throwable;

/**
 * RetryFailedAction — Delegates retry execution to the canonical Resilience RetryExecutor.
 *
 * FailureBoundary owns the policy-to-options mapping.
 * Resilience owns the retry loop, backoff calculation, and result tracking.
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
        $delayMs = $policy->retryDelayMs;
        $backoff = $policy->retryBackoff;
        $jitter = $policy->retryJitter;

        // retryMaxAttempts = total attempts including the original (already failed).
        // RetryExecutor handles only the remaining retry attempts.
        $remainingAttempts = max(1, $maxAttempts - 1);

        $options = new RetryOptions(
            attempts       : $remainingAttempts,
            backoffMs      : $delayMs,
            backoffStrategy: $backoff,
            jitter         : $jitter,
        );

        $executor = new RetryExecutor(
            operation   : $originalAction,
            retryOptions: $options,
        );

        $result = $executor->execute();

        if ($result->success) {
            // +1 for the original attempt that triggered the retry
            return FailurePipelineResult::retried($result->result, $result->attempts + 1);
        }

        // Retry exhausted — rethrow the last failure
        throw $result->lastException ?? $failure;
    }
}
