<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Lifecycle\WarmCache;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\CacheTtl;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use DateInterval;

final readonly class WarmCache
{
    public function __construct(
        private CacheStore $cacheStore,
        private Clock $clock,
        private CacheTtl $cacheTtl = new CacheTtl(),
    ) {
    }

    /**
     * @param  iterable<string, mixed>  $entries
     */
    public function warm(iterable $entries, int|DateInterval|null $ttl = null): int
    {
        $count = 0;

        foreach ($entries as $key => $loader) {
            $cacheKey = CacheKey::create(key: $key);
            $value = is_callable($loader) ? $loader() : $loader;

            $expiresAt = $this->cacheTtl->calculateExpiresAt(ttl: $ttl, clock: $this->clock)
                ?? $this->clock->now()->add(
                    duration: Duration::ofSeconds(seconds: 3600),
                );

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $expiresAt,
                clock    : $this->clock,
            );

            $this->cacheStore->write(
                cacheKey        : $cacheKey,
                storedCacheRecord: new StoredCacheRecord(value: $value, cachedValueLifecycle: $lifecycle),
            );

            $count++;
        }

        return $count;
    }
}
