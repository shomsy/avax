<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\FileCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final class L2DistributedCache
{
    public static function withCapacity(Clock $clock, string $basePath, int $maxSize): TieredCache
    {
        $cacheTier = CacheTier::l2(maxSize: $maxSize);
        $filesystem = new Filesystem();
        $fileCacheStore = self::create(basePath: $basePath, filesystem: $filesystem, clock: $clock);

        $tieredCache = new TieredCache($clock, $cacheTier);
        $tieredCache->registerTier(cacheTier: $cacheTier, cacheStore: $fileCacheStore);

        return $tieredCache;
    }

    public static function create(
        string $basePath, Filesystem $filesystem, Clock|null $clock = null,
    ): FileCacheStore {
        return new FileCacheStore(basePath: $basePath, filesystem: $filesystem, clock: $clock ?? new SystemClock());
    }
}
