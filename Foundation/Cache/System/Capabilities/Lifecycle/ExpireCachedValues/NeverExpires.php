<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class NeverExpires implements CacheExpiration
{
    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock                 $clock
    ) : Timestamp|null
    {
        return null;
    }

    public function isExpired(
        Timestamp|null $expiresAt,
        Clock          $clock
    ) : bool
    {
        return false;
    }
}