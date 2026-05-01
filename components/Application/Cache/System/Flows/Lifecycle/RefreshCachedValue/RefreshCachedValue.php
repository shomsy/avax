<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Lifecycle\RefreshCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues\CacheTtl;
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
        private CacheStore $cacheStore,
        private Clock                    $clock,
        private ShouldRefreshCachedValue $shouldRefreshCachedValue
        = new ShouldRefreshCachedValue(
            clock: new SystemClock,
        ),
        private CacheTtl                 $cacheTtl = new CacheTtl,
    ) {}

    public function refreshIfNeeded(
        CacheKey              $cacheKey,
        callable              $loader,
        int|DateInterval|null $ttl = null,
    ) : mixed
    {
        $result = $this->cacheStore->read(key: $cacheKey, clock: $this->clock);

        if ($result instanceof CacheStoreRecordWasMissing) {
            return $this->refresh(key: $cacheKey, loader: $loader, ttl: $ttl);
        }

        if ($this->shouldRefreshCachedValue->shouldRefresh(lifecycle: $result->record->lifecycle)) {
            return $this->refresh(key: $cacheKey, loader: $loader, ttl: $ttl);
        }

        return $result->value();
    }

    public function refresh(
        CacheKey $cacheKey,
        callable              $loader,
        int|DateInterval|null $ttl = null,
    ) : mixed
    {
        try {
            $value = $loader();

            $this->cacheStore->write(
                key   : $cacheKey,
                record: new StoredCacheRecord(
                            value    : $value,
                            lifecycle: CachedValueLifecycle::create(
                                           createdAt: $this->clock->now(),
                                           expiresAt: $this->cacheTtl->calculateExpiresAt(ttl: $ttl, clock: $this->clock)
                                                          ?? $this->clock->now()->add(
                                               duration: Duration::ofSeconds(seconds: 3600),
                                           ),
                                           clock    : $this->clock,
                                       ),
                        ),
            );

            return $value;
        } catch (Throwable) {
            return null;
        }
    }
}
