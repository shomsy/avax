<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\StoreCachedValue;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods\CacheTtl;
use Avax\Cache\System\Capabilities\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use DateInterval;
use Throwable;

final readonly class StoreCachedValue
{
    public function __construct(
        private CacheStore    $store,
        private Clock         $clock,
        private ?CacheMetrics $metrics = null,
        private CacheTtl      $ttlCalculator = new CacheTtl()
    ) {}

    public function store(CacheKey $key, mixed $value, null|int|DateInterval $ttl = null) : bool
    {
        $startTime = hrtime(as_integer: true);

        try {
            $expiresAt     = $this->ttlCalculator->calculateExpiresAt($ttl, $this->clock);
            $defaultExpiry = $expiresAt ?? $this->clock->now()->add(
                Duration::ofSeconds(86400)
            );

            $lifecycle = CachedValueLifecycle::create(
                createdAt: $this->clock->now(),
                expiresAt: $defaultExpiry,
                clock    : $this->clock
            );

            $record = new StoredCacheRecord(
                value    : $value,
                lifecycle: $lifecycle
            );

            $this->store->write($key, $record);

            $this->recordLatency($startTime);
            $this->metrics?->recordWrite();

            return true;
        } catch (Throwable $e) {
            $this->metrics?->recordStoreFailure();

            return false;
        }
    }

    private function recordLatency(int $startTime) : void
    {
        if ($this->metrics === null) {
            return;
        }

        $endTime             = hrtime(as_integer: true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        $this->metrics->recordLatency($latencyMicroseconds);
    }
}