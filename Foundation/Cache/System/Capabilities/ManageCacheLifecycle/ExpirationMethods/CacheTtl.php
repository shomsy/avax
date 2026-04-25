<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\SystemClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class CacheTtl implements CacheExpiration
{
    public function __construct(
        private Clock $clock = new SystemClock()
    ) {}

    public static function toSeconds(null|int|DateInterval $ttl) : ?int
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            return (int) DateInterval::createFromDateString(
                (string) $ttl->s + ($ttl->i * 60) + ($ttl->h * 3600) + ($ttl->d * 86400)
            )->s;
        }

        return $ttl > 0 ? $ttl : null;
    }

    public function calculateExpiresAt(
        null|int|DateInterval $ttl,
        ?Clock                $clock = null
    ) : ?Timestamp
    {
        $clock ??= $this->clock;

        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            return $clock->now()->add(
                Duration::fromDateInterval($ttl)
            );
        }

        if ($ttl <= 0) {
            return null;
        }

        return $clock->now()->add(
            Duration::ofSeconds($ttl)
        );
    }

    public function isExpired(
        ?Timestamp $expiresAt,
        ?Clock     $clock = null
    ) : bool
    {
        $clock ??= $this->clock;

        if ($expiresAt === null) {
            return false;
        }

        return $clock->now()->isAfter($expiresAt);
    }
}