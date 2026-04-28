<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\Operations\ClearCache;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheNamespace;
use Avax\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class ClearCache
{
    public function __construct(
        private CacheStore        $store,
        private Clock             $clock,
        private CacheMetrics|null $metrics = null
    ) {}

    public function clearNamespace(CacheNamespace $namespace) : int
    {
        return $this->clear();
    }

    public function clear() : bool
    {
        try {
            $this->store->clear();

            $this->metrics?->recordDelete();

            return true;
        } catch (Throwable $e) {
            $this->metrics?->recordStoreFailure();

            return false;
        }
    }
}