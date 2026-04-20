<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Login\RateLimit;

/**
 * In-memory rate limit storage for tests and lightweight deployments.
 */
final class InMemoryLoginRateLimitStorage implements LoginRateLimitStorageInterface
{
    /** @var array<string, int> */
    private array $attempts = [];

    /** @var array<string, int> */
    private array $lastAttemptTimes = [];

    public function get(string $identifier) : int
    {
        return $this->attempts[$identifier] ?? 0;
    }

    public function increment(string $identifier) : void
    {
        $this->attempts[$identifier]         = ($this->attempts[$identifier] ?? 0) + 1;
        $this->lastAttemptTimes[$identifier] = time();
    }

    public function reset(string $identifier) : void
    {
        unset($this->attempts[$identifier], $this->lastAttemptTimes[$identifier]);
    }

    public function getLastAttemptTime(string $identifier) : int
    {
        return $this->lastAttemptTimes[$identifier] ?? 0;
    }
}
