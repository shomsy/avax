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
        private CacheStore        $store,
        private Clock             $clock,
        private CacheMetrics|null $metrics = null,
        private CacheTtl          $ttlCalculator = new CacheTtl()
    ) {}

    public function store(CacheKey $key, mixed $value, int|DateInterval|null $ttl = null) : bool
    {
        $startTime = hrtime(as_integer: true);

        try {
            $expiresAt     = $this->ttlCalculator->calculateExpiresAt(ttl: $ttl, clock: $this->clock);
            $defaultExpiry = $expiresAt ?? $this->clock->now()->add(
                duration: Duration::ofSeconds(seconds: 86400)
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

            $this->store->write(key: $key, record: $record);

            $this->recordLatency(startTime: $startTime);
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

        $this->metrics->recordLatency(microseconds: $latencyMicroseconds);
    }
}