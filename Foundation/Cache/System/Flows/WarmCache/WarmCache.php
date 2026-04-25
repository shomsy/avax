<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\WarmCache;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods\CacheTtl;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use DateInterval;

final readonly class WarmCache
{
    public function __construct(
        private CacheStore $store,
        private Clock      $clock,
        private CacheTtl   $ttlCalculator = new CacheTtl()
    ) {}

    public function warm(iterable $entries, null|int|DateInterval $ttl = null) : int
    {
        $count = 0;

        foreach ($entries as $key => $loader) {
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create($key);
            $value    = is_callable($loader) ? $loader() : $loader;

            $expiresAt = $this->ttlCalculator->calculateExpiresAt($ttl, $this->clock)
                ?? $this->clock->now()->add(
                    Duration::ofSeconds(3600)
                );

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $expiresAt,
                clock    : $this->clock
            );

            $this->store->write(
                $cacheKey,
                new StoredCacheRecord(value: $value, lifecycle: $lifecycle)
            );

            $count++;
        }

        return $count;
    }
}