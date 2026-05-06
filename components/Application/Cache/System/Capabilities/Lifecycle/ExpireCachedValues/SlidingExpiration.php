<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;
use Override;

final readonly class SlidingExpiration implements CacheExpiration
{
    public function __construct(
        private int $windowSeconds,
    ) {
    }

    public static function seconds(int $seconds): self
    {
        return new self(windowSeconds: $seconds);
    }

    #[Override]
    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock $clock,
    ): ?Timestamp {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            $duration = Duration::fromDateInterval(dateInterval: $ttl);
        } else {
            $duration = Duration::ofSeconds(seconds: $ttl);
        }

        return $clock->now()->add(duration: $duration);
    }

    public function slide(?Timestamp $timestamp, Clock $clock): ?Timestamp
    {
        if (! $timestamp instanceof Timestamp) {
            return null;
        }

        return $clock->now()->add(
            duration: Duration::ofSeconds(seconds: $this->windowSeconds),
        );
    }

    #[Override]
    public function isExpired(
        ?Timestamp $timestamp,
        Clock $clock,
    ): bool {
        if (! $timestamp instanceof Timestamp) {
            return true;
        }

        return $clock->now()->isAfter(other: $timestamp);
    }
}
