<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;
use Override;

final readonly class ExpiresAfter implements CacheExpiration
{
    public function __construct(
        private Duration $duration,
    ) {
    }

    public static function seconds(int $seconds): self
    {
        return new self(duration: Duration::ofSeconds(seconds: $seconds));
    }

    public static function milliseconds(int $milliseconds): self
    {
        return new self(duration: Duration::ofMilliseconds(milliseconds: $milliseconds));
    }

    #[Override]
    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock $clock,
    ): ?Timestamp {
        return $clock->now()->add(duration: $this->duration);
    }

    #[Override]
    public function isExpired(
        ?Timestamp $timestamp,
        Clock $clock,
    ): bool {
        if (! $timestamp instanceof Timestamp) {
            return true;
        }

        return $clock->now()->isAfter(other: $timestamp);
    }
}
