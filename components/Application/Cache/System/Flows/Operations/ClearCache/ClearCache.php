<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Operations\ClearCache;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheNamespace;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class ClearCache
{
    public function __construct(
        private CacheStore        $cacheStore,
        private CacheMetrics|null $cacheMetrics = null,
    ) {}

    public function clearNamespace() : int
    {
        return $this->clear();
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
