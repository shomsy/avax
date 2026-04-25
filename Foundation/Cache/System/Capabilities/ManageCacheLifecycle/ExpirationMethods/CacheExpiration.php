<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

interface CacheExpiration
{
    public function calculateExpiresAt(
        null|int|DateInterval $ttl,
        Clock                 $clock
    ) : ?Timestamp;

    public function isExpired(
        ?Timestamp $expiresAt,
        Clock      $clock
    ) : bool;
}