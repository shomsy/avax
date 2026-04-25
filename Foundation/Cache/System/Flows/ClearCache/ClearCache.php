<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\ClearCache;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheNamespace;
use Avax\Cache\System\Capabilities\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class ClearCache
{
    public function __construct(
        private CacheStore    $store,
        private Clock         $clock,
        private ?CacheMetrics $metrics = null
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