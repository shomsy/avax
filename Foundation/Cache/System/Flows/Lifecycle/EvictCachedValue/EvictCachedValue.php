<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\Lifecycle\EvictCachedValue;

use Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Cache\System\Foundation\Time\Clock;
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