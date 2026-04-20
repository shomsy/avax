<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa\Challenge;

/**
 * In-memory MFA attempt limit storage for tests and lightweight deployments.
 */
final class InMemoryAttemptLimitStorage implements AttemptLimitStorageInterface
{
    /** @var array<string, int> */
    private array $attempts = [];

    /** @var array<string, int> */
    private array $lastAttemptTimes = [];

    public function get(string $key) : int
    {
        return $this->attempts[$key] ?? 0;
    }

    public function increment(string $key, int $timestamp) : void
    {
        $this->attempts[$key]         = ($this->attempts[$key] ?? 0) + 1;
        $this->lastAttemptTimes[$key] = $timestamp;
    }

    public function reset(string $key) : void
    {
        unset($this->attempts[$key], $this->lastAttemptTimes[$key]);
    }

    public function getLastAttemptTime(string $key) : int
    {
        return $this->lastAttemptTimes[$key] ?? 0;
    }
}
