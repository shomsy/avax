<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Lifecycle\InvalidateCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues\InvalidationReason;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheTag;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final readonly class InvalidateCachedValue
{
    public function __construct(
        private CacheStore        $cacheStore,
        private CacheMetrics|null $cacheMetrics = null,
    ) {}

    public function invalidateByKey(CacheKey $cacheKey) : void
    {
        $this->invalidate(key: $cacheKey, reason: InvalidationReason::EXPLICIT);
    }

    public function invalidate(CacheKey $key, InvalidationReason $reason = InvalidationReason::EXPLICIT) : void
    {
        $this->cacheStore->forget(key: $key);

        $this->cacheMetrics?->recordInvalidation();
    }

    public function invalidateByKeys(iterable $keys) : int
    {
        $count = 0;

        foreach ($keys as $key) {
            $key = $key instanceof CacheKey ? $key : CacheKey::create(key: $key);
            $this->cacheStore->forget(key: $key);
            $count++;
        }

        $this->cacheMetrics?->recordInvalidation();

        return $count;
    }

    public function invalidateByTag() : int
    {
        $this->cacheMetrics?->recordInvalidation();
        return 0;
    }

    public function invalidateByNamespace() : int
    {
        $this->cacheMetrics?->recordInvalidation();
        return 0;
    }
}
