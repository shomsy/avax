<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Operations\ClearCache;

use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Throwable;

final readonly class ClearCache
{
    public function __construct(
        private CacheStore $cacheStore,
        private CacheMetrics|null $cacheMetrics = null,
    ) {}

    public function clearNamespace() : int
    {
        return $this->clear() ? 1 : 0;
    }

    public function clear() : bool
    {
        try {
            $this->cacheStore->clear();

            $this->cacheMetrics?->recordDelete();

            return true;
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return false;
        }
    }
}
