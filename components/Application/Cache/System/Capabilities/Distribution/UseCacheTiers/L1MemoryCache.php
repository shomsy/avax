<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final class L1MemoryCache
{
    public static function withCapacity(Clock $clock, int $maxSize) : TieredCache
    {
        $cacheTier          = CacheTier::l1(maxSize: $maxSize);
        $inMemoryCacheStore = self::create(clock: $clock);

        $tieredCache = new TieredCache($clock, $cacheTier);
        $tieredCache->registerTier(tier: $cacheTier, store: $inMemoryCacheStore);

        return $tieredCache;
    }

    public static function create(Clock $clock = null) : InMemoryCacheStore
    {
        return new InMemoryCacheStore(clock: $clock ?? new SystemClock());
    }
}
