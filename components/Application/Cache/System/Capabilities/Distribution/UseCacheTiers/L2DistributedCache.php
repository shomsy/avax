<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\FileCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final class L2DistributedCache
{
    public static function withCapacity(Clock $clock, string $basePath, int $maxSize): TieredCache
    {
        $cacheTier      = CacheTier::l2(maxSize: $maxSize);
        $fileCacheStore = self::create(basePath: $basePath, clock: $clock);

        $tieredCache = new TieredCache($clock, $cacheTier);
        $tieredCache->registerTier(cacheTier: $cacheTier, cacheStore: $fileCacheStore);

        return $tieredCache;
    }

    public static function create(
        string $basePath,
        ?Clock $clock = null,
    ): FileCacheStore {
        return new FileCacheStore(basePath: $basePath, clock: $clock ?? new SystemClock());
    }
}
