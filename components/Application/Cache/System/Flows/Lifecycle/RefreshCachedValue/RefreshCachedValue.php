<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Lifecycle\RefreshCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\CacheTtl;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\RefreshCachedValues\RefreshPolicy;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\RefreshCachedValues\ShouldRefreshCachedValue;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use DateInterval;
use Throwable;

final readonly class RefreshCachedValue
{
    public function __construct(
        private CacheStore               $store,
        private Clock                    $clock,
        private ShouldRefreshCachedValue $shouldRefresh
        = new ShouldRefreshCachedValue(
            clock: new SystemClock()
        ),
        private CacheTtl                 $ttlCalculator = new CacheTtl()
    ) {}

    public function refreshIfNeeded(
        CacheKey              $key,
        callable              $loader,
        int|DateInterval|null $ttl = null,
        RefreshPolicy         $policy = RefreshPolicy::DO_NOT_REFRESH
    ) : mixed
    {
        $result = $this->store->read(key: $key, clock: $this->clock);

        if ($result instanceof CacheStoreRecordWasMissing) {
            return $this->refresh(key: $key, loader: $loader, ttl: $ttl, policy: $policy);
        }

        if ($this->shouldRefresh->shouldRefresh(lifecycle: $result->record->lifecycle)) {
            return $this->refresh(key: $key, loader: $loader, ttl: $ttl, policy: $policy);
        }

        return $result->value();
    }

    public function refresh(
        CacheKey              $key,
        callable              $loader,
        int|DateInterval|null $ttl = null,
        RefreshPolicy         $policy = RefreshPolicy::DO_NOT_REFRESH
    ) : mixed
    {
        try {
            $value = $loader();

            $this->store->write(
                key   : $key,
                record: new StoredCacheRecord(
                            value    : $value,
                            lifecycle: CachedValueLifecycle::create(
                                           createdAt: $this->clock->now(),
                                           expiresAt: $this->ttlCalculator->calculateExpiresAt(ttl: $ttl, clock: $this->clock)
                                                          ?? $this->clock->now()->add(
                                               duration: Duration::ofSeconds(seconds: 3600)
                                           ),
                                           clock    : $this->clock
                                       )
                        )
            );

            return $value;
        } catch (Throwable $e) {
            return null;
        }
    }
}