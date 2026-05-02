<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Resilience;

/**
 * RetryPolicy - Configurable retry policy for HTTP requests.
 *
 * Supports configurable attempts, delay, and backoff strategies
 * including exponential backoff with optional jitter.
 *
 * Usage:
 *   $policy = RetryPolicy::exponential(attempts: 3, baseDelayMs: 1000);
 *   $policy = RetryPolicy::fixed(attempts: 5, delayMs: 2000);
 *   $policy = new RetryPolicy(
 *       attempts: 3,
 *       baseDelayMs: 500,
 *       backoffMultiplier: 2.0,
 *       maxDelayMs: 10000,
 *       jitter: true,
 *       retryOnStatus: [500, 502, 503, 504],
 *   );
 */
final readonly class RetryPolicy
{
    /**
     * @param int       $attempts               Maximum number of retry attempts (not including the initial attempt)
     * @param int       $baseDelayMs            Base delay between retries in milliseconds
     * @param float     $backoffMultiplier      Multiplier for exponential backoff
     * @param int       $maxDelayMs             Maximum delay cap in milliseconds
     * @param bool      $jitter                 Whether to add random jitter to delays
     * @param list<int> $retryOnStatus          HTTP status codes that should trigger a retry
     * @param bool      $retryOnTimeout         Whether to retry on timeout
     * @param bool      $retryOnConnectionError Whether to retry on connection errors
     */
    public function __construct(
        public int $attempts = 3,
        public int $baseDelayMs = 1000,
        public float $backoffMultiplier = 2.0,
        public int $maxDelayMs = 30_000,
        public bool $jitter = false,
        public array $retryOnStatus = [500, 502, 503, 504],
        public bool $retryOnTimeout = true,
        public bool $retryOnConnectionError = true,
    ) {}

    /**
     * Create an exponential backoff retry policy.
     *
     * Delays: baseDelay, baseDelay*multiplier, baseDelay*multiplier^2, ...
     */
    public static function exponential(
        int $attempts = 3,
        int $baseDelayMs = 1000,
        float $multiplier = 2.0,
        int $maxDelayMs = 30_000,
    ) : self
    {
        return new self(
            attempts         : $attempts,
            baseDelayMs      : $baseDelayMs,
            backoffMultiplier: $multiplier,
            maxDelayMs       : $maxDelayMs,
        );
    }

    /**
     * Create a fixed delay retry policy.
     *
     * All retries have the same delay.
     */
    public static function fixed(
        int $attempts = 3,
        int $delayMs = 1000,
    ) : self
    {
        return new self(
            attempts         : $attempts,
            baseDelayMs      : $delayMs,
            backoffMultiplier: 1.0,
        );
    }

    /**
     * Create a linear backoff retry policy.
     *
     * Delays: baseDelay, baseDelay*2, baseDelay*3, ...
     */
    public static function linear(
        int $attempts = 3,
        int $baseDelayMs = 1000,
        int $maxDelayMs = 30_000,
    ) : self
    {
        return new self(
            attempts         : $attempts,
            baseDelayMs      : $baseDelayMs,
            backoffMultiplier: 1.0,
            maxDelayMs       : $maxDelayMs,
        );
    }

    /**
     * Create a no-retry policy (for testing or explicit disabling).
     */
    public static function none() : self
    {
        return new self(attempts: 0);
    }

    /**
     * Check if a response status code should trigger a retry.
     */
    public function shouldRetryStatus(int $statusCode) : bool
    {
        return in_array($statusCode, $this->retryOnStatus, true);
    }

    /**
     * Check if a timeout should trigger a retry.
     */
    public function shouldRetryTimeout() : bool
    {
        return $this->retryOnTimeout;
    }

    /**
     * Check if a connection error should trigger a retry.
     */
    public function shouldRetryConnectionError() : bool
    {
        return $this->retryOnConnectionError;
    }

    /**
     * Check if there are remaining retry attempts.
     *
     * @param int $currentAttempt The current attempt number (1-based)
     */
    public function hasRemainingAttempts(int $currentAttempt) : bool
    {
        return $currentAttempt <= $this->attempts;
    }

    /**
     * Get the total maximum delay across all attempts.
     */
    public function getTotalMaxDelay() : int
    {
        $total = 0;
        for ($i = 1; $i <= $this->attempts; $i++) {
            $total += $this->delayForAttempt($i);
        }

        return $total;
    }

    /**
     * Calculate the delay for a given retry attempt.
     *
     * @param int $attempt The current attempt number (1-based)
     * @return int Delay in milliseconds
     */
    public function delayForAttempt(int $attempt): int
    {
        if ($attempt < 1 || $attempt > $this->attempts) {
            return 0;
        }

        // Calculate base delay with backoff
        $delay = (int) ($this->baseDelayMs * ($this->backoffMultiplier ** ($attempt - 1)));

        // Cap at maximum delay
        $delay = min($delay, $this->maxDelayMs);

        // Add jitter if enabled (±25% random variation)
        if ($this->jitter) {
            $jitterRange = (int) ($delay * 0.25);
            $delay += random_int(-$jitterRange, $jitterRange);
            $delay = max(1, $delay); // Ensure positive delay
        }

        return $delay;
    }
}
