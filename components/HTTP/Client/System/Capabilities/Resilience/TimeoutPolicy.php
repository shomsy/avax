<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Resilience;

/**
 * TimeoutPolicy - Configurable timeout settings for HTTP requests.
 *
 * Provides granular timeout configuration including connect timeout,
 * transfer timeout, and overall request timeout.
 *
 * Usage:
 *   $policy = new TimeoutPolicy(
 *       connectTimeoutMs: 5000,
 *       transferTimeoutMs: 15000,
 *       totalTimeoutMs: 30000,
 *   );
 *   $policy = TimeoutPolicy::strict(5000);  // All timeouts at 5s
 *   $policy = TimeoutPolicy::relaxed(60000); // All timeouts at 60s
 */
final readonly class TimeoutPolicy
{
    /**
     * @param  int  $connectTimeoutMs  Timeout for establishing connection (ms)
     * @param  int  $transferTimeoutMs  Timeout for receiving data/transfer (ms)
     * @param  int  $timeoutMs  Overall total timeout (ms)
     * @param  bool  $enforce  Whether to strictly enforce timeouts
     */
    public function __construct(
        public int $connectTimeoutMs = 5000,
        public int $transferTimeoutMs = 30000,
        public int $timeoutMs = 30000,
        public bool $enforce = true,
    ) {
    }

    /**
     * Create a strict timeout policy (all timeouts at the same value).
     *
     * @param  int  $timeoutMs  The timeout value in milliseconds
     */
    public static function strict(int $timeoutMs = 5000): self
    {
        return new self(
            connectTimeoutMs : $timeoutMs,
            transferTimeoutMs: $timeoutMs,
            timeoutMs        : $timeoutMs,
        );
    }

    /**
     * Create a relaxed timeout policy (longer timeouts).
     *
     * @param  int  $timeoutMs  The timeout value in milliseconds (default: 60s)
     */
    public static function relaxed(int $timeoutMs = 60_000): self
    {
        return new self(
            connectTimeoutMs : $timeoutMs,
            transferTimeoutMs: $timeoutMs,
            timeoutMs        : $timeoutMs,
        );
    }

    /**
     * Create a policy optimized for fast requests.
     */
    public static function fast(): self
    {
        return new self(
            connectTimeoutMs : 2000,
            transferTimeoutMs: 5000,
            timeoutMs        : 5000,
        );
    }

    /**
     * Create a policy optimized for streaming/long requests.
     */
    public static function streaming(): self
    {
        return new self(
            connectTimeoutMs : 10000,
            transferTimeoutMs: 300_000,
            timeoutMs        : 300_000,
        );
    }

    /**
     * Create a disabled timeout policy (no timeouts).
     * Use with caution - can cause requests to hang indefinitely.
     */
    public static function none(): self
    {
        return new self(
            connectTimeoutMs : 0,
            transferTimeoutMs: 0,
            timeoutMs        : 0,
            enforce          : false,
        );
    }

    /**
     * Get the minimum timeout value (for connect timeout).
     */
    public function minTimeout(): int
    {
        return $this->connectTimeoutMs;
    }

    /**
     * Get the maximum timeout value.
     */
    public function maxTimeout(): int
    {
        return max($this->connectTimeoutMs, $this->transferTimeoutMs, $this->timeoutMs);
    }
}
