<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\UseCacheTiers;

use Avax\Cache\System\Capabilities\StoreCachedValues\FileCacheStore;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final class L2DistributedCache
{
    public static function withCapacity(Clock $clock, string $basePath, int $maxSize) : TieredCache
    {
        $tier  = CacheTier::l2(maxSize: $maxSize);
        $store = self::create($basePath, $clock);

        $tieredCache = new TieredCache($clock, $tier);
        $tieredCache->registerTier($tier, $store);

        return $tieredCache;
    }

    public static function create(
        string $basePath,
        ?Clock $clock = null
    ) : FileCacheStore
    {
        return new FileCacheStore($basePath, $clock ?? new SystemClock());
    }
}