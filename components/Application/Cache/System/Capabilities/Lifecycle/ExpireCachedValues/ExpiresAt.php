<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;
use Override;

final readonly class ExpiresAt implements CacheExpiration
{
    public function __construct(private Timestamp $timestamp)
    {
    }

    public static function secondsFromNow(int $seconds, Clock $clock) : self
    {
        return new self(
            expiresAt: $clock->now()->add(
                         duration: Duration::ofSeconds(seconds: $seconds),
                     ),
        );
    }

    public static function atTimestamp(int $timestamp) : self
    {
        return new self(
            expiresAt: Timestamp::fromUnixTime(timestamp: $timestamp),
        );
    }

    #[Override]
    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock $clock,
    ) : Timestamp|null
    {
        return $this->timestamp;
    }

    #[Override]
    public function isExpired(
        Timestamp|null $expiresAt,
        Clock $clock,
    ) : bool
    {
        if (! $expiresAt instanceof Timestamp) {
            return true;
        }

        return $clock->now()->isAfter(other: $expiresAt);
    }
}
