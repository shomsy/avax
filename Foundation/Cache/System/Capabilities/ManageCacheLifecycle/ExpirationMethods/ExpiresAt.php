<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class ExpiresAt implements CacheExpiration
{
    public function __construct(
        private Timestamp $expiresAt
    ) {}

    public static function secondsFromNow(int $seconds, Clock $clock) : self
    {
        return new self(
            $clock->now()->add(
                Duration::ofSeconds($seconds)
            )
        );
    }

    public static function atTimestamp(int $timestamp) : self
    {
        return new self(
            Timestamp::fromUnixTime($timestamp)
        );
    }

    public function calculateExpiresAt(
        null|int|DateInterval $ttl,
        Clock                 $clock
    ) : ?Timestamp
    {
        return $this->expiresAt;
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