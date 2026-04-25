<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\InvalidateCachedValue;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheTag;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationMethods\InvalidationReason;
use Avax\Cache\System\Capabilities\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Foundation\Time\Clock;

final readonly class InvalidateCachedValue
{
    public function __construct(
        private CacheStore    $store,
        private Clock         $clock,
        private ?CacheMetrics $metrics = null
    ) {}

    public function invalidateByKey(CacheKey $key) : void
    {
        $this->invalidate($key, InvalidationReason::EXPLICIT);
    }

    public function invalidate(CacheKey $key, InvalidationReason $reason = InvalidationReason::EXPLICIT) : void
    {
        $this->store->forget($key);

        $this->metrics?->recordInvalidation();
    }

    public function invalidateByKeys(iterable $keys) : int
    {
        $count = 0;

        foreach ($keys as $key) {
            $key = $key instanceof CacheKey ? $key : CacheKey::create($key);
            $this->store->forget($key);
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