<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\EvictCachedValue;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\ReplacementPolicies\ChooseCachedValueForReplacement;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\ReplacementPolicies\LeastRecentlyUsedReplacement;
use Avax\Cache\System\Capabilities\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class EvictCachedValue
{
    public function __construct(
        private CacheStore                      $store,
        private Clock                           $clock,
        private ChooseCachedValueForReplacement $policy = new LeastRecentlyUsedReplacement(),
        private ?CacheMetrics                   $metrics = null
    ) {}

    public function evict(CacheKey $key) : bool
    {
        try {
            $this->store->forget($key);
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
            $keyToEvict = $this->policy->choose($entries);

            if ($keyToEvict === null) {
                break;
            }

            $cacheKey = CacheKey::create($keyToEvict);
            $this->store->forget($cacheKey);
            $evicted++;

            unset($entries[$keyToEvict]);
        }

        $this->metrics?->recordEviction();

        return $evicted;
    }
}