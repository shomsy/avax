<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class ExpiresAfter implements CacheExpiration
{
    public function __construct(
        private Duration $duration
    ) {}

    public static function seconds(int $seconds) : self
    {
        return new self(duration: Duration::ofSeconds(seconds: $seconds));
    }

    public static function milliseconds(int $milliseconds) : self
    {
        return new self(duration: Duration::ofMilliseconds(milliseconds: $milliseconds));
    }

    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock                 $clock
    ) : Timestamp|null
    {
        return $clock->now()->add(duration: $this->duration);
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