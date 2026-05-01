<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle;

/**
 * In-memory throttling store for tests and lightweight compositions.
 */
final class InMemoryAttemptThrottleStore implements AttemptThrottleStoreInterface
{
    /** @var array<string, int> */
    private array $attempts = [];

    /** @var array<string, int> */
    private array $lastAttemptTimes = [];

    public function get(string $key): int
    {
        return $this->attempts[$key] ?? 0;
    }

    public function increment(string $key, int $timestamp): void
    {
        $this->attempts[$key] = ($this->attempts[$key] ?? 0) + 1;
        $this->lastAttemptTimes[$key] = $timestamp;
    }

    public function reset(string $key): void
    {
        unset($this->attempts[$key], $this->lastAttemptTimes[$key]);
    }

    public function getLastAttemptTime(string $key): int
    {
        return $this->lastAttemptTimes[$key] ?? 0;
    }
}
