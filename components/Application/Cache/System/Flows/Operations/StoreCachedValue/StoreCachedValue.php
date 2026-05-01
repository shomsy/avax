<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Operations\StoreCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\CacheTtl;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use DateInterval;
use Throwable;

final readonly class StoreCachedValue
{
    public function __construct(
        private CacheStore $cacheStore,
        private Clock    $clock,
        private ?CacheMetrics $cacheMetrics = null,
        private CacheTtl $cacheTtl = new CacheTtl(),
    ) {}

    public function store(CacheKey $cacheKey, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        $startTime = hrtime(true);

        try {
            $expiresAt = $this->cacheTtl->calculateExpiresAt(ttl: $ttl, clock: $this->clock);
            $defaultExpiry = $expiresAt ?? $this->clock->now()->add(
                duration: Duration::ofSeconds(seconds: 86400),
            );

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $defaultExpiry,
                clock    : $this->clock,
            );

            $storedCacheRecord = new StoredCacheRecord(
                value    : $value,
                lifecycle: $lifecycle,
            );

            $this->cacheStore->write(key: $cacheKey, record: $storedCacheRecord);

            $this->recordLatency(startTime: $startTime);
            $this->cacheMetrics?->recordWrite();

            return true;
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return false;
        }
    }

    private function recordLatency(int $startTime) : void
    {
        if (! $this->cacheMetrics instanceof CacheMetrics) {
            return;
        }

        $endTime = hrtime(true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        $this->cacheMetrics->recordLatency(microseconds: $latencyMicroseconds);
    }
}
