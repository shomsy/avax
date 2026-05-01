<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;
use Override;

final readonly class CacheTtl implements CacheExpiration
{
    public function __construct(
        private Clock $clock = new SystemClock(),
    ) {}

    public static function toSeconds(int|DateInterval|null $ttl) : int|null
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            return Duration::fromDateInterval(interval: $ttl)->toSeconds();
        }

        return $ttl > 0 ? $ttl : null;
    }

    #[Override]
    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock $clock = null,
    ) : Timestamp|null
    {
        $clock ??= $this->clock;

        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            return $clock->now()->add(
                duration: Duration::fromDateInterval(interval: $ttl),
            );
        }

        if ($ttl <= 0) {
            return null;
        }

        return $clock->now()->add(
            duration: Duration::ofSeconds(seconds: $ttl),
        );
    }

    #[Override]
    public function isExpired(
        Timestamp|null $expiresAt,
        Clock $clock = null,
    ) : bool
    {
        $clock ??= $this->clock;

        if (! $expiresAt instanceof Timestamp) {
            return false;
        }

        return $clock->now()->isAfter(other: $expiresAt);
    }
}
