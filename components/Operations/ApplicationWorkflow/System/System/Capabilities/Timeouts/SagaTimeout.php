<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\System\Capabilities\Timeouts;

/**
 * Defines timeout configuration for saga step execution.
 */
final readonly class SagaTimeout
{
    public function __construct(
        public int $timeoutMs = 30000,
    ) {}

    /**
     * Create a timeout in milliseconds.
     */
    public static function milliseconds(int $ms) : self
    {
        return new self(timeoutMs: $ms);
    }

    /**
     * Create a timeout in seconds.
     */
    public static function seconds(int $seconds) : self
    {
        return new self(timeoutMs: $seconds * 1000);
    }

    /**
     * Create a timeout in minutes.
     */
    public static function minutes(int $minutes) : self
    {
        return new self(timeoutMs: $minutes * 60 * 1000);
    }

    /**
     * Check if a given duration exceeds this timeout.
     */
    public function isExceeded(int $elapsedMs) : bool
    {
        return $elapsedMs > $this->timeoutMs;
    }

    /**
     * Get the timeout in seconds.
     */
    public function toSeconds() : float
    {
        return $this->timeoutMs / 1000;
    }
}
