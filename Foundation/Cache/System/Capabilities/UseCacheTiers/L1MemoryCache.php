<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\UseCacheTiers;

use Avax\Cache\System\Capabilities\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final class L1MemoryCache
{
    public static function withCapacity(Clock $clock, int $maxSize) : TieredCache
    {
        $tier  = CacheTier::l1(maxSize: $maxSize);
        $store = self::create($clock);

        $tieredCache = new TieredCache($clock, $tier);
        $tieredCache->registerTier($tier, $store);

        return $tieredCache;
    }

    public static function create(?Clock $clock = null) : InMemoryCacheStore
    {
        return new InMemoryCacheStore($clock ?? new SystemClock());
    }
}