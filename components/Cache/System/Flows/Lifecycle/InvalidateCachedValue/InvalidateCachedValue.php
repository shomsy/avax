<?php

declare(strict_types=1);

namespace components\Cache\System\Flows\Lifecycle\InvalidateCachedValue;

use components\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues\InvalidationReason;
use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheTag;
use components\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use components\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use components\Cache\System\Foundation\Time\Clock;

final readonly class InvalidateCachedValue
{
    public function __construct(
        private CacheStore        $store,
        private Clock             $clock,
        private CacheMetrics|null $metrics = null
    ) {}

    public function invalidateByKey(CacheKey $key) : void
    {
        $this->invalidate(key: $key, reason: InvalidationReason::EXPLICIT);
    }

    public function invalidate(CacheKey $key, InvalidationReason $reason = InvalidationReason::EXPLICIT) : void
    {
        $this->store->forget(key: $key);

        $this->metrics?->recordInvalidation();
    }

    public function invalidateByKeys(iterable $keys) : int
    {
        $count = 0;

        foreach ($keys as $key) {
            $key = $key instanceof CacheKey ? $key : CacheKey::create(key: $key);
            $this->store->forget(key: $key);
            $count++;
        }

        $this->metrics?->recordInvalidation();

        return $count;
    }

    public function invalidateByTag(CacheTag $tag) : int
    {
        $this->metrics?->recordInvalidation();

        return 0;
    }

    public function invalidateByNamespace(string $namespace) : int
    {
        $this->metrics?->recordInvalidation();

        return 0;
    }
}