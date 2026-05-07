<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\Retries;

/**
 * Defines retry policy for saga step execution.
 */
final readonly class RetryPolicy
{
    public function __construct(
        public int    $maxAttempts = 3,
        public int    $backoffMs = 100,
        public string $backoffType = 'exponential',
    ) {}

    /**
     * Create a no-retry policy (execute once only).
     */
    public static function none() : self
    {
        return new self(maxAttempts: 1, backoffMs: 0);
    }

    /**
     * Create a policy with immediate retries (no delay).
     */
    public static function immediate(int $maxAttempts = 3) : self
    {
        return new self(maxAttempts: $maxAttempts, backoffMs: 0);
    }

    /**
     * Create a policy with exponential backoff.
     */
    public static function exponential(int $maxAttempts = 3, int $baseBackoffMs = 100) : self
    {
        return new self(
            maxAttempts: $maxAttempts,
            backoffMs  : $baseBackoffMs,
            backoffType: 'exponential',
        );
    }

    /**
     * Create a policy with linear backoff.
     */
    public static function linear(int $maxAttempts = 3, int $backoffMs = 100) : self
    {
        return new self(
            maxAttempts: $maxAttempts,
            backoffMs  : $backoffMs,
            backoffType: 'linear',
        );
    }

    /**
     * Calculate the delay in milliseconds for a given attempt number.
     */
    public function getDelayForAttempt(int $attempt) : int
    {
        return match ($this->backoffType) {
            'exponential' => (int) ($this->backoffMs * (2 ** ($attempt - 1))),
            'linear'      => $this->backoffMs * $attempt,
            default       => 0,
        };
    }

    /**
     * Check if another retry attempt is allowed.
     */
    public function canRetry(int $currentAttempt) : bool
    {
        return $currentAttempt < $this->maxAttempts;
    }
}
