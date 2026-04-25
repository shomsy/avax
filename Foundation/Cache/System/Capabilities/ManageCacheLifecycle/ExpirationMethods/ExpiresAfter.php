<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods;

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
        return new self(Duration::ofSeconds($seconds));
    }

    public static function milliseconds(int $milliseconds) : self
    {
        return new self(Duration::ofMilliseconds($milliseconds));
    }

    public function calculateExpiresAt(
        null|int|DateInterval $ttl,
        Clock                 $clock
    ) : ?Timestamp
    {
        return $clock->now()->add($this->duration);
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