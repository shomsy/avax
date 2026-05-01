<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use DateInterval;

interface CacheExpiration
{
    public function calculateExpiresAt(
        int|DateInterval|null $ttl,
        Clock $clock,
    ) : ?Timestamp;

    public function isExpired(
        ?Timestamp $expiresAt,
        Clock $clock,
    ) : bool;
}
