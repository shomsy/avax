<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class ExpiresAt implements CacheExpiration
{
    public function __construct(
        private Timestamp $expiresAt
    ) {}

    public static function secondsFromNow(int $seconds, Clock $clock) : self
    {
        return new self(
            expiresAt: $clock->now()->add(
                         duration: Duration::ofSeconds(seconds: $seconds)
                     )
        );
    }

    public static function atTimestamp(int $timestamp) : self
    {
        return new self(
            expiresAt: Timestamp::fromUnixTime(timestamp: $timestamp)
        );
    }

    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock                 $clock
    ) : Timestamp|null
    {
        return $this->expiresAt;
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