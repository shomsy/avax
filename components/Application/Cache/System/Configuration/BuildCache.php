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
    public function __construct(private Clock $clock = new SystemClock()) {}

    public function inMemory(?CacheConfiguration $cacheConfiguration = null) : AvaxCache
    {
        $inMemoryCacheStore = new InMemoryCacheStore(clock: $this->clock);

        return $this->fromStore(store: $inMemoryCacheStore, config: $cacheConfiguration);
    }

    public function fromStore(CacheStore $cacheStore, ?CacheConfiguration $cacheConfiguration = null) : AvaxCache
    {
        $cacheConfiguration ??= new CacheConfiguration();

        $metrics = $cacheConfiguration->enableMetrics ? new CacheMetrics() : null;

        return new AvaxCache(
            store      : $cacheStore,
            clock      : $this->clock,
            metrics    : $metrics,
            stalePolicy: $cacheConfiguration->stalePolicy,
        );
    }

    public function inDirectory(string $directory, ?CacheConfiguration $cacheConfiguration = null) : AvaxCache
    {
        return $this->file(basePath: $directory, config: $cacheConfiguration);
    }

    public function file(string $basePath, ?CacheConfiguration $cacheConfiguration = null) : AvaxCache
    {
        $fileCacheStore = new FileCacheStore(
            basePath: $basePath,
            clock   : $this->clock,
        );

        return $this->fromStore(store: $fileCacheStore, config: $cacheConfiguration);
    }

    public function redis(
        string              $host = '127.0.0.1',
        int                 $port = 6379,
        ?CacheConfiguration $cacheConfiguration = null,
    ) : AvaxCache
    {
        $redisCacheStore = new RedisCacheStore(
            host : $host,
            port : $port,
            clock: $this->clock,
        );

        return $this->fromStore(store: $redisCacheStore, config: $cacheConfiguration);
    }

    public function tiered(
        CacheStore          $l1,
        CacheStore          $l2,
        ?CacheConfiguration $cacheConfiguration = null,
    ) : AvaxCache
    {
        $chainCacheStore = new ChainCacheStore($l1, $l2);

        return $this->fromStore(store: $chainCacheStore, config: $cacheConfiguration);
    }
}
