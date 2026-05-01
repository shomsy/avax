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
    public function __construct(private readonly CacheSource $cacheSource, private readonly CacheStore|null $cacheStore = null, private readonly SourceSyncPolicy $sourceSyncPolicy = SourceSyncPolicy::CACHE_ASIDE, private DeferredSourceWrite|null $deferredSourceWrite = null)
    {
    }

    public function write(CacheKey $cacheKey, mixed $value) : void
    {
        match ($this->sourceSyncPolicy) {
            SourceSyncPolicy::WRITE_THROUGH => $this->writeThrough(key: $cacheKey, value: $value),
            SourceSyncPolicy::WRITE_AROUND  => $this->writeAround(key: $cacheKey, value: $value),
            SourceSyncPolicy::WRITE_BEHIND  => $this->writeBehind(key: $cacheKey, value: $value),
            SourceSyncPolicy::CACHE_ASIDE   => $this->cacheAsideWrite(key: $cacheKey, value: $value),
            SourceSyncPolicy::NO_SYNC       => $this->writeToSourceOnly(),
        };
    }

    private function writeThrough(CacheKey $cacheKey, mixed $value) : void
    {
        $writeValueToSource = new WriteValueToSource(source: $this->cacheSource);
        $writeValueToSource->write(key: $cacheKey, value: $value);

        if ($this->cacheStore instanceof CacheStore) {
            $this->invalidateCache(key: $cacheKey);
        }
    }

    private function invalidateCache(CacheKey $cacheKey) : void
    {
        $this->cacheStore?->forget(key: $cacheKey);
    }

    private function writeAround(CacheKey $cacheKey, mixed $value) : void
    {
        $writeValueToSource = new WriteValueToSource(source: $this->cacheSource);
        $writeValueToSource->write(key: $cacheKey, value: $value);

        $this->invalidateCache(key: $cacheKey);
    }

    private function writeBehind(CacheKey $cacheKey, mixed $value) : void
    {
        $this->invalidateCache(key: $cacheKey);

        if (! $this->deferredSourceWrite instanceof DeferredSourceWrite) {
            $this->deferredSourceWrite = new DeferredSourceWrite(source: $this->cacheSource);
        }

        $this->deferredSourceWrite->queueFromCacheKey(key: $cacheKey, value: $value);
    }

    private function cacheAsideWrite(CacheKey $cacheKey, mixed $value) : void
    {
        $writeValueToSource = new WriteValueToSource(source: $this->cacheSource);
        $writeValueToSource->write(key: $cacheKey, value: $value);
    }

    private function writeToSourceOnly() : void {}

    public function delete(CacheKey $cacheKey) : void
    {
        match ($this->sourceSyncPolicy) {
            SourceSyncPolicy::WRITE_THROUGH => $this->deleteThrough(key: $cacheKey),
            SourceSyncPolicy::WRITE_AROUND,
            SourceSyncPolicy::CACHE_ASIDE  => $this->invalidateCache(key: $cacheKey),
            SourceSyncPolicy::WRITE_BEHIND => $this->invalidateCache(key: $cacheKey),
            SourceSyncPolicy::NO_SYNC      => $this->deleteFromSource(key: $cacheKey),
        };
    }

    private function deleteThrough(CacheKey $cacheKey) : void
    {
        $deleteValueFromSource = new DeleteValueFromSource(source: $this->cacheSource);
        $deleteValueFromSource->delete(key: $cacheKey);

        $this->invalidateCache(key: $cacheKey);
    }

    private function deleteFromSource(CacheKey $cacheKey) : void
    {
        $deleteValueFromSource = new DeleteValueFromSource(source: $this->cacheSource);
        $deleteValueFromSource->delete(key: $cacheKey);
    }

    public function loadFromSource(CacheKey $cacheKey) : mixed
    {
        $cacheSourceKey = CacheSourceKey::create(
            key      : $cacheKey->fullKey(),
            namespace: $cacheKey->namespace,
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
