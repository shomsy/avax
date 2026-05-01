<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\SourceSyncPolicies;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\CacheSource;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\CacheSourceKey;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\DeferredSourceWrite;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\DeleteValueFromSource;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\WriteValueToSource;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;

final class SourceSyncCoordinator
{
    private CacheSource $cacheSource;

    private CacheStore|null $cacheStore;

    private SourceSyncPolicy $sourceSyncPolicy;

    private DeferredSourceWrite|null $deferredSourceWrite;

    public function __construct(
        CacheSource              $source,
        CacheStore|null          $cache = null,
        SourceSyncPolicy         $policy = SourceSyncPolicy::CACHE_ASIDE,
        DeferredSourceWrite|null $deferredWrite = null,
    )
    {
        $this->cacheSource         = $source;
        $this->cacheStore          = $cache;
        $this->sourceSyncPolicy    = $policy;
        $this->deferredSourceWrite = $deferredWrite;
    }

    public function write(CacheKey $key, mixed $value) : void
    {
        match ($this->sourceSyncPolicy) {
            SourceSyncPolicy::WRITE_THROUGH => $this->writeThrough(key: $key, value: $value),
            SourceSyncPolicy::WRITE_AROUND  => $this->writeAround(key: $key, value: $value),
            SourceSyncPolicy::WRITE_BEHIND  => $this->writeBehind(key: $key, value: $value),
            SourceSyncPolicy::CACHE_ASIDE   => $this->cacheAsideWrite(key: $key, value: $value),
            SourceSyncPolicy::NO_SYNC       => $this->writeToSourceOnly(),
        };
    }

    private function writeThrough(CacheKey $key, mixed $value) : void
    {
        $writeValueToSource = new WriteValueToSource(source: $this->cacheSource);
        $writeValueToSource->write(key: $key, value: $value);

        if ($this->cacheStore !== null) {
            $this->invalidateCache(key: $key);
        }
    }

    private function invalidateCache(CacheKey $key) : void
    {
        $this->cacheStore?->forget(key: $key);
    }

    private function writeAround(CacheKey $key, mixed $value) : void
    {
        $writeValueToSource = new WriteValueToSource(source: $this->cacheSource);
        $writeValueToSource->write(key: $key, value: $value);

        $this->invalidateCache(key: $key);
    }

    private function writeBehind(CacheKey $key, mixed $value) : void
    {
        $this->invalidateCache(key: $key);

        if ($this->deferredSourceWrite === null) {
            $this->deferredSourceWrite = new DeferredSourceWrite(source: $this->cacheSource);
        }

        $this->deferredSourceWrite->queueFromCacheKey(key: $key, value: $value);
    }

    private function cacheAsideWrite(CacheKey $key, mixed $value) : void
    {
        $writeValueToSource = new WriteValueToSource(source: $this->cacheSource);
        $writeValueToSource->write(key: $key, value: $value);
    }

    private function writeToSourceOnly() : void {}

    public function delete(CacheKey $key) : void
    {
        match ($this->sourceSyncPolicy) {
            SourceSyncPolicy::WRITE_THROUGH => $this->deleteThrough(key: $key),
            SourceSyncPolicy::WRITE_AROUND,
            SourceSyncPolicy::CACHE_ASIDE   => $this->invalidateCache(key: $key),
            SourceSyncPolicy::WRITE_BEHIND  => $this->invalidateCache(key: $key),
            SourceSyncPolicy::NO_SYNC       => $this->deleteFromSource(key: $key),
        };
    }

    private function deleteThrough(CacheKey $key) : void
    {
        $deleteValueFromSource = new DeleteValueFromSource(source: $this->cacheSource);
        $deleteValueFromSource->delete(key: $key);

        $this->invalidateCache(key: $key);
    }

    private function deleteFromSource(CacheKey $key) : void
    {
        $deleteValueFromSource = new DeleteValueFromSource(source: $this->cacheSource);
        $deleteValueFromSource->delete(key: $key);
    }

    public function loadFromSource(CacheKey $key) : mixed
    {
        $cacheSourceKey = CacheSourceKey::create(
            key      : $key->fullKey(),
            namespace: $key->namespace,
        );

        return $this->cacheSource->load($cacheSourceKey);
    }

    public function shouldPopulateCacheOnMiss() : bool
    {
        return match ($this->sourceSyncPolicy) {
            SourceSyncPolicy::CACHE_ASIDE,
            SourceSyncPolicy::WRITE_THROUGH,
            SourceSyncPolicy::WRITE_BEHIND => true,
            SourceSyncPolicy::NO_SYNC,
            SourceSyncPolicy::WRITE_AROUND => false,
        };
    }

    public function flushPendingWrites() : int
    {
        return $this->deferredSourceWrite?->flush() ?? 0;
    }

    public function pendingWritesCount() : int
    {
        return $this->deferredSourceWrite?->pendingCount() ?? 0;
    }
}
