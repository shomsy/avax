<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

final readonly class CachedValueLifecycle
{
    public function __construct(
        public Timestamp $createdAt,
        public Timestamp $lastAccessedAt,
        public Timestamp $expiresAt,
        public Timestamp $refreshedAt,
        public int $hitCount = 0,
        public int $refreshCount = 0,
    ) {}

    public static function create(
        Timestamp $createdAt,
        Timestamp $expiresAt,
        Clock $clock,
    ) : self
    {
        return new self(
            createdAt     : $createdAt,
            lastAccessedAt: $clock->now(),
            expiresAt     : $expiresAt,
            refreshedAt   : $createdAt,
        );
    }

    public function withAccessed(Clock $clock) : self
    {
        return new self(
            createdAt     : $this->createdAt,
            lastAccessedAt: $clock->now(),
            expiresAt     : $this->expiresAt,
            refreshedAt   : $this->refreshedAt,
            hitCount      : $this->hitCount + 1,
            refreshCount  : $this->refreshCount,
        );
    }

    public function withRefreshed(Timestamp $refreshedAt, Timestamp $newExpiresAt) : self
    {
        return new self(
            createdAt     : $this->createdAt,
            lastAccessedAt: $this->lastAccessedAt,
            expiresAt     : $newExpiresAt,
            refreshedAt   : $refreshedAt,
            hitCount      : $this->hitCount,
            refreshCount  : $this->refreshCount + 1,
        );
    }

    public function isExpired(Clock $clock) : bool
    {
        return $clock->now()->isAfter(other: $this->expiresAt);
    }

    public function timeToLive(Clock $clock) : int
    {
        $now = $clock->now();

        if ($now->isAfter(other: $this->expiresAt)) {
            return 0;
        }

        $duration = $this->expiresAt->difference(other: $now);

        return $duration->toSeconds();
    }

    public function age(Clock $clock) : int
    {
        return $clock->now()->difference(other: $this->createdAt)->toSeconds();
    }

    public function idleTime(Clock $clock) : int
    {
        return $clock->now()->difference(other: $this->lastAccessedAt)->toSeconds();
    }
}
