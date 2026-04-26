<?php

declare(strict_types=1);

namespace Avax\Cache\System\Configuration;

use Avax\Cache\System\AvaxCache;
use Avax\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\ChainCacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\FileCacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final readonly class BuildCache
{
    public function __construct(private Clock $clock = new SystemClock()) {}

    public function inMemory(CacheConfiguration|null $config = null) : AvaxCache
    {
        $store = new InMemoryCacheStore(clock: $this->clock);

        return $this->fromStore(store: $store, config: $config);
    }

    public function fromStore(CacheStore $store, CacheConfiguration|null $config = null) : AvaxCache
    {
        $config ??= new CacheConfiguration();

        $metrics = $config->enableMetrics ? new CacheMetrics() : null;

        return new AvaxCache(
            store      : $store,
            clock      : $this->clock,
            metrics    : $metrics,
            stalePolicy: $config->stalePolicy
        );
    }

    public function file(string $basePath, CacheConfiguration|null $config = null) : AvaxCache
    {
        $store = new FileCacheStore(
            basePath: $basePath,
            clock   : $this->clock
        );

        return $this->fromStore(store: $store, config: $config);
    }

    public function tiered(
        CacheStore          $l1,
        CacheStore          $l2,
        CacheConfiguration|null $config = null
    ) : AvaxCache
    {
        $store = new ChainCacheStore($l1, $l2);

        return $this->fromStore(store: $store, config: $config);
    }
}