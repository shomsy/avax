<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration;

use Avax\Components\Application\Cache\System\AvaxCache;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\ChainCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\FileCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\RedisCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final readonly class BuildCache
{
    public function __construct(private Clock $clock = new SystemClock())
    {
    }

    public function inMemory(CacheConfiguration|null $config = null) : AvaxCache
    {
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->clock);

        return $this->fromStore(store: $inMemoryCacheStore, config: $config);
    }

    public function fromStore(CacheStore $store, CacheConfiguration|null $config = null) : AvaxCache
    {
        $config ??= new CacheConfiguration();

        $metrics = $config->enableMetrics ? new CacheMetrics() : null;

        return new AvaxCache(
            cacheStore      : $store,
            clock           : $this->clock,
            cacheMetrics    : $metrics,
            staleValuePolicy: $config->staleValuePolicy,
        );
    }

    public function inDirectory(string $directory, CacheConfiguration|null $config = null) : AvaxCache
    {
        return $this->file(basePath: $directory, config: $config);
    }

    public function file(string $basePath, CacheConfiguration|null $config = null) : AvaxCache
    {
        $fileCacheStore = new FileCacheStore(
            basePath: $basePath,
            clock   : $this->clock,
        );

        return $this->fromStore(store: $fileCacheStore, config: $config);
    }

    public function redis(
        string $host = '127.0.0.1',
        int $port = 6379, CacheConfiguration|null $config = null,
    ): AvaxCache {
        $redisCacheStore = new RedisCacheStore(
            host : $host,
            port : $port,
            clock: $this->clock,
        );

        return $this->fromStore(store: $redisCacheStore, config: $config);
    }

    public function tiered(
        CacheStore $l1,
        CacheStore $l2, CacheConfiguration|null $config = null,
    ): AvaxCache {
        $chainCacheStore = new ChainCacheStore($l1, $l2);

        return $this->fromStore(store: $chainCacheStore, config: $config);
    }
}
