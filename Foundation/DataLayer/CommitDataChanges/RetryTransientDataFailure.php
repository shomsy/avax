<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

use Throwable;

/**
 * RetryTransientDataFailure - retries only transient failures that are safe to replay.
 */
final readonly class RetryTransientDataFailure
{
    public function __construct(private DetectDeadlock $detectDeadlock = new DetectDeadlock()) {}

    /**
     * @throws Throwable
     */
    public function retry(callable $work, DataTransactionPolicy $policy) : mixed
    {
        if ($policy->retryTransientFailures && ! $policy->idempotent) {
            throw DataTransactionFailure::unsafeRetryPolicy();
        }

        $attempts = max(1, $policy->maxAttempts);

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $work($attempt);
            } catch (Throwable $failure) {
                $canRetry = $policy->retryTransientFailures
                    && $attempt < $attempts
                    && $this->detectDeadlock->detect(failure: $failure);

                if (! $canRetry) {
                    throw $failure;
                }
            }
        }

        return null;
    }
}
