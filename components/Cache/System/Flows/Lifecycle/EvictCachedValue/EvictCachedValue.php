<?php

declare(strict_types=1);

namespace components\Cache\System\Flows\Lifecycle\EvictCachedValue;

use components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use components\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use components\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use components\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class EvictCachedValue
{
    public function __construct(
        private CacheStore                      $store,
        private Clock                           $clock,
        private ChooseCachedValueForReplacement $policy = new LeastRecentlyUsedReplacement(),
        private CacheMetrics|null               $metrics = null
    ) {}

    public function evict(CacheKey $key) : bool
    {
        try {
            $this->store->forget(key: $key);
            $this->metrics?->recordEviction();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function evictUntilCapacityIsSafe(
        array $entries,
        int   $maxCapacity
    ) : int
    {
        $evicted = 0;

        while ( count($entries) > $maxCapacity ) {
            $keyToEvict = $this->policy->choose(entries: $entries);

            if ($keyToEvict === null) {
                break;
            }

            $cacheKey = CacheKey::create(key: $keyToEvict);
            $this->store->forget(key: $cacheKey);
            $evicted++;

            unset($entries[$keyToEvict]);
        }

        $this->metrics?->recordEviction();

        return $evicted;
    }
}