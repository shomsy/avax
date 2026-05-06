<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Lifecycle\InvalidateCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;

final readonly class InvalidateCachedValue
{
    public function __construct(
        private CacheStore $cacheStore,
        private ?CacheMetrics $cacheMetrics = null,
    ) {
    }

    public function invalidateByKey(CacheKey $cacheKey): void
    {
        $this->invalidate(cacheKey: $cacheKey);
    }

    public function invalidate(CacheKey $cacheKey): void
    {
        $this->cacheStore->forget(cacheKey: $cacheKey);

        $this->cacheMetrics?->recordInvalidation();
    }

    /**
     * @param  iterable<CacheKey|string>  $keys
     */
    public function invalidateByKeys(iterable $keys): int
    {
        $count = 0;

        foreach ($keys as $key) {
            $key = $key instanceof CacheKey ? $key : CacheKey::create(key: $key);
            $this->cacheStore->forget(cacheKey: $key);
            $count++;
        }

        $this->cacheMetrics?->recordInvalidation();

        return $count;
    }

    public function invalidateByTag(): int
    {
        $this->cacheMetrics?->recordInvalidation();

        return 0;
    }

    public function invalidateByNamespace(): int
    {
        $this->cacheMetrics?->recordInvalidation();

        return 0;
    }
}
