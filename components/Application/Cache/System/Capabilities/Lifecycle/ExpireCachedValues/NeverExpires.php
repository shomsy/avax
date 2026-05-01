<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;
use Override;

final readonly class NeverExpires implements CacheExpiration
{
    #[Override]
    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock $clock,
    ) : ?Timestamp
    {
        return null;
    }

    #[Override]
    public function isExpired(
        ?Timestamp $timestamp,
        Clock $clock,
    ) : bool
    {
        return false;
    }
}
