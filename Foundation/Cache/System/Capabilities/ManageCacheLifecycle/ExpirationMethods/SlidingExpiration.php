<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class SlidingExpiration implements CacheExpiration
{
    public function __construct(
        private int $windowSeconds
    ) {}

    public static function seconds(int $seconds) : self
    {
        return new self($seconds);
    }

    public function calculateExpiresAt(
        null|int|DateInterval $ttl,
        Clock                 $clock
    ) : ?Timestamp
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            $duration = Duration::fromDateInterval($ttl);
        } else {
            $duration = Duration::ofSeconds($ttl);
        }

        return $clock->now()->add($duration);
    }

    public function slide(?Timestamp $currentExpiresAt, Clock $clock) : ?Timestamp
    {
        if ($currentExpiresAt === null) {
            return null;
        }

        return $clock->now()->add(
            Duration::ofSeconds($this->windowSeconds)
        );
    }

    public function isExpired(
        ?Timestamp $expiresAt,
        Clock      $clock
    ) : bool
    {
        if ($expiresAt === null) {
            return true;
        }

        return $clock->now()->isAfter($expiresAt);
    }
}