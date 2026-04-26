<?php

declare(strict_types=1);

namespace components\Cache\System\Flows\Lifecycle\WarmCache;

use components\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use components\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\CacheTtl;
use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use components\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use components\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use components\Cache\System\Foundation\Time\Clock;
use components\Cache\System\Foundation\Time\Duration;
use DateInterval;

final readonly class WarmCache
{
    public function __construct(
        private CacheStore $store,
        private Clock      $clock,
        private CacheTtl   $ttlCalculator = new CacheTtl()
    ) {}

    public function warm(iterable $entries, int|DateInterval|null $ttl = null) : int
    {
        $count = 0;

        foreach ($entries as $key => $loader) {
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create(key: $key);
            $value    = is_callable($loader) ? $loader() : $loader;

            $expiresAt = $this->ttlCalculator->calculateExpiresAt(ttl: $ttl, clock: $this->clock)
                ?? $this->clock->now()->add(
                    duration: Duration::ofSeconds(seconds: 3600)
                );

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $expiresAt,
                clock    : $this->clock
            );

            $this->store->write(
                key   : $cacheKey,
                record: new StoredCacheRecord(value: $value, lifecycle: $lifecycle)
            );

            $count++;
        }

        return $count;
    }
}