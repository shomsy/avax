<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class SlidingExpiration implements CacheExpiration
{
    public function __construct(
        private int $windowSeconds
    ) {}

    public static function seconds(int $seconds) : self
    {
        return new self(windowSeconds: $seconds);
    }

    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock                 $clock
    ) : Timestamp|null
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            $duration = Duration::fromDateInterval(interval: $ttl);
        } else {
            $duration = Duration::ofSeconds(seconds: $ttl);
        }

        return $clock->now()->add(duration: $duration);
    }

    public function slide(Timestamp|null $currentExpiresAt, Clock $clock) : Timestamp|null
    {
        if ($currentExpiresAt === null) {
            return null;
        }

        return $clock->now()->add(
            duration: Duration::ofSeconds(seconds: $this->windowSeconds)
        );
    }

    public function isExpired(
        Timestamp|null $expiresAt,
        Clock          $clock
    ) : bool
    {
        if ($expiresAt === null) {
            return true;
        }

        return $clock->now()->isAfter(other: $expiresAt);
    }
}