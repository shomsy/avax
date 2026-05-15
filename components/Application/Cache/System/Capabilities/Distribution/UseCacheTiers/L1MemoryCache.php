<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final class L1MemoryCache
{
    public static function withCapacity(Clock $clock, int $maxSize): TieredCache
    {
        $cacheTier = CacheTier::l1(maxSize: $maxSize);
        $inMemoryCacheStore = self::create(clock: $clock);

        $tieredCache = new TieredCache($clock, $cacheTier);
        $tieredCache->registerTier(cacheTier: $cacheTier, cacheStore: $inMemoryCacheStore);

        return $tieredCache;
    }

    public static function create(Clock $clock) : InMemoryCacheStore
    {
        return new InMemoryCacheStore(
            clock                          : $clock,
            chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(),
        );
    }
}
