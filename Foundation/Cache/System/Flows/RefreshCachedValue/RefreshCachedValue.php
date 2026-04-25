<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\RefreshCachedValue;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\ExpirationMethods\CacheTtl;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\RefreshPolicies\RefreshPolicy;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\RefreshPolicies\ShouldRefreshCachedValue;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\SystemClock;
use DateInterval;
use Throwable;

final readonly class RefreshCachedValue
{
    public function __construct(
        private CacheStore               $store,
        private Clock                    $clock,
        private ShouldRefreshCachedValue $shouldRefresh
        = new ShouldRefreshCachedValue(
            new SystemClock()
        ),
        private CacheTtl                 $ttlCalculator = new CacheTtl()
    ) {}

    public function refreshIfNeeded(
        CacheKey              $key,
        callable              $loader,
        null|int|DateInterval $ttl = null,
        RefreshPolicy         $policy = RefreshPolicy::DO_NOT_REFRESH
    ) : mixed
    {
        $result = $this->store->read($key, $this->clock);

        if ($result instanceof CacheStoreRecordWasMissing) {
            return $this->refresh($key, $loader, $ttl, $policy);
        }

        if ($this->shouldRefresh->shouldRefresh($result->record->lifecycle)) {
            return $this->refresh($key, $loader, $ttl, $policy);
        }

        return $result->value();
    }

    public function refresh(
        CacheKey              $key,
        callable              $loader,
        null|int|DateInterval $ttl = null,
        RefreshPolicy         $policy = RefreshPolicy::DO_NOT_REFRESH
    ) : mixed
    {
        try {
            $value = $loader();

            $this->store->write(
                $key,
                new StoredCacheRecord(
                    value    : $value,
                    lifecycle: CachedValueLifecycle::create(
                                   createdAt: $this->clock->now(),
                                   expiresAt: $this->ttlCalculator->calculateExpiresAt($ttl, $this->clock)
                                                  ?? $this->clock->now()->add(
                                       Duration::ofSeconds(3600)
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