<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

use PDOException;
use Throwable;

/**
 * Transaction retry policy for handling deadlocks and temporary failures.
 *
 * Configures maximum attempts, delay strategy with exponential backoff,
 * and which exception types should trigger a retry.
 */
final readonly class RetryPolicy
{
    /**
     * @param int                           $maxAttempts Maximum number of retry attempts (total attempts = maxAttempts)
     * @param int                           $baseDelayMs Base delay in milliseconds before first retry
     * @param int                           $maxDelayMs  Maximum delay cap in milliseconds
     * @param float                         $multiplier  Exponential backoff multiplier
     * @param list<class-string<Throwable>> $retryOn     Exception classes that should trigger a retry
     * @param list<string>                  $errorCodes  Database error codes that should trigger a retry
     */
    public function __construct(
        public int $maxAttempts = 3,
        public int $baseDelayMs = 100,
        public int $maxDelayMs = 5000,
        public float $multiplier = 2.0,
        public array $retryOn = [],
        public array $errorCodes = [],
    ) {}

    /**
     * Creates a policy optimized for deadlock retries.
     */
    public static function forDeadlocks(int $maxAttempts = 5): self
    {
        return new self(
            maxAttempts: $maxAttempts,
            baseDelayMs: 100,
            maxDelayMs : 5000,
            multiplier : 2.0,
            retryOn    : [PDOException::class],
            errorCodes : ['40001', '40P01'],
        );
    }

    /**
     * Creates a policy optimized for network error retries.
     */
    public static function forNetworkErrors(int $maxAttempts = 3): self
    {
        return new self(
            maxAttempts: $maxAttempts,
            baseDelayMs: 500,
            maxDelayMs : 10000,
            multiplier : 1.5,
            retryOn    : [PDOException::class],
        );
    }

    /**
     * Creates a policy for transient/timeout errors.
     */
    public static function forTimeouts(int $maxAttempts = 3): self
    {
        return new self(
            maxAttempts: $maxAttempts,
            baseDelayMs: 200,
            maxDelayMs : 3000,
            multiplier : 2.0,
            retryOn    : [PDOException::class],
        );
    }

    /**
     * Creates a no-retry policy (fail immediately on any error).
     */
    public static function noRetry(): self
    {
        return new self(maxAttempts: 1);
    }

    /**
     * Determines if the given exception should trigger a retry.
     *
     * @param Throwable $exception The exception to check
     * @param int       $attempt   The current attempt number (1-based, before retry)
     */
    public function shouldRetry(Throwable $exception, int $attempt): bool
    {
        if ($attempt >= $this->maxAttempts) {
            return false;
        }

        if (! empty($this->retryOn)) {
            foreach ($this->retryOn as $exceptionClass) {
                if ($exception instanceof $exceptionClass) {
                    return true;
                }
            }
        }

        if (! empty($this->errorCodes) && $exception instanceof PDOException) {
            $code = (string) $exception->getCode();
            foreach ($this->errorCodes as $errorCode) {
                if ($code === $errorCode) {
                    return true;
                }
            }
        }

        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'deadlock')) {
            return true;
        }

        if (str_contains($message, 'serialization failure')) {
            return true;
        }

        if (str_contains($message, 'lock wait timeout')) {
            return true;
        }

        if (str_contains($message, 'connection refused')) {
            return true;
        }

        if (str_contains($message, 'timeout')) {
            return true;
        }

        if (str_contains($message, 'too many connections')) {
            return true;
        }

        if (str_contains($message, 'try restarting transaction')) {
            return true;
        }

        return false;
    }

    /**
     * Calculates the delay in milliseconds before the next retry attempt.
     *
     * Uses exponential backoff: baseDelayMs * multiplier^attempt
     *
     * @param int $attempt The attempt number (0-based)
     *
     * @return int Delay in milliseconds, capped at maxDelayMs
     */
    public function getDelayMs(int $attempt): int
    {
        $delay = (int) ($this->baseDelayMs * pow($this->multiplier, $attempt));

        return min($delay, $this->maxDelayMs);
    }

    /**
     * Returns whether this policy would retry on any exception (has retry criteria).
     */
    public function hasRetryCriteria(): bool
    {
        return ! empty($this->retryOn) || ! empty($this->errorCodes);
    }

    /**
     * Creates a copy of this policy with a different maximum attempts value.
     */
    public function withMaxAttempts(int $maxAttempts): self
    {
        return new self(
            maxAttempts: $maxAttempts,
            baseDelayMs: $this->baseDelayMs,
            maxDelayMs : $this->maxDelayMs,
            multiplier : $this->multiplier,
            retryOn    : $this->retryOn,
            errorCodes : $this->errorCodes,
        );
    }
}
