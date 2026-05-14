<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline;

/**
 * Rate limiter interface for controlling request frequency.
 */
interface RateLimiterInterface
{
    /**
     * Determine if a request can be attempted.
     */
    public function canAttempt(string $key, int $maxAttempts, int $decaySeconds): bool;

    /**
     * Record a failed attempt.
     */
    public function recordFailedAttempt(string $key, int $maxAttempts, int $decaySeconds): void;

    /**
     * Get the remaining attempts.
     */
    public function remainingAttempts(string $key, int $maxAttempts, int $decaySeconds): int;

    /**
     * Get seconds until the limiter resets.
     */
    public function availableIn(string $key, int $maxAttempts, int $decaySeconds): int;

    /**
     * Clear all attempts for a key.
     */
    public function clear(string $key): void;
}
