<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\FileCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final class L2DistributedCache
{
    public static function withCapacity(Clock $clock, string $basePath, int $maxSize) : TieredCache
    {
        $tier  = CacheTier::l2(maxSize: $maxSize);
        $store = self::create(basePath: $basePath, clock: $clock);

        $tieredCache = new TieredCache($clock, $tier);
        $tieredCache->registerTier(tier: $tier, store: $store);

        return $tieredCache;
    }

    public static function create(
        string     $basePath,
        Clock|null $clock = null
    ) : FileCacheStore
    {
        return new FileCacheStore(basePath: $basePath, clock: $clock ?? new SystemClock());
    }
}