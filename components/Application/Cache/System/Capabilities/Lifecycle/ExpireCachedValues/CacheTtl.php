<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class CacheTtl implements CacheExpiration
{
    public function __construct(
        private Clock $clock = new SystemClock()
    ) {}

    public static function toSeconds(int|DateInterval|null $ttl) : int|null
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            return (int) DateInterval::createFromDateString(
                datetime: (string) $ttl->s + ($ttl->i * 60) + ($ttl->h * 3600) + ($ttl->d * 86400)
            )->s;
        }

        return $ttl > 0 ? $ttl : null;
    }

    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock|null            $clock = null
    ) : Timestamp|null
    {
        $clock ??= $this->clock;

        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            return $clock->now()->add(
                duration: Duration::fromDateInterval(interval: $ttl)
            );
        }

        if ($ttl <= 0) {
            return null;
        }

        return $clock->now()->add(
            duration: Duration::ofSeconds(seconds: $ttl)
        );
    }

    public function isExpired(
        Timestamp|null $expiresAt,
        Clock|null     $clock = null
    ) : bool
    {
        $clock ??= $this->clock;

        if ($expiresAt === null) {
            return false;
        }

        return $clock->now()->isAfter(other: $expiresAt);
    }
}