<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

final readonly class NeverExpires implements CacheExpiration
{
    public function calculateExpiresAt(
        null|int|DateInterval $ttl,
        Clock                 $clock
    ) : ?Timestamp
    {
        return null;
    }

    public function isExpired(
        ?Timestamp $expiresAt,
        Clock      $clock
    ) : bool
    {
        return false;
    }
}