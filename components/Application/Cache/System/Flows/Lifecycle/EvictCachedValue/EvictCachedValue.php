<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Lifecycle\EvictCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Throwable;

final readonly class EvictCachedValue
{
    public function __construct(
        private CacheStore        $cacheStore,
        private ChooseCachedValueForReplacement $chooseCachedValueForReplacement = new LeastRecentlyUsedReplacement(),
        private CacheMetrics|null $cacheMetrics = null,
    ) {}

    public function evict(CacheKey $cacheKey) : bool
    {
        try {
            $this->cacheStore->forget(key: $cacheKey);
            $this->cacheMetrics?->recordEviction();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function evictUntilCapacityIsSafe(
        array $entries,
        int $maxCapacity,
    ) : int
    {
        $evicted = 0;

        while ( count($entries) > $maxCapacity ) {
            $keyToEvict = $this->chooseCachedValueForReplacement->choose(entries: $entries);

            if ($keyToEvict === null) {
                break;
            }

            $cacheKey = CacheKey::create(key: $keyToEvict);
            $this->cacheStore->forget(key: $cacheKey);
            $evicted++;

            unset($entries[$keyToEvict]);
        }

        $this->cacheMetrics?->recordEviction();

        return $evicted;
    }
}
