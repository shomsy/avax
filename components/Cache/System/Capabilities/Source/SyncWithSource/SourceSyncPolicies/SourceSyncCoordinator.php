<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\SyncWithSource\SourceSyncPolicies;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\CacheSource;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\CacheSourceKey;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\DeferredSourceWrite;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\DeleteValueFromSource;
use Avax\Cache\System\Capabilities\Source\SyncWithSource\WriteValueToSource;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;

final readonly class SourceSyncCoordinator
{
    public function __construct(
        private CacheSource              $source,
        private CacheStore|null          $cache = null,
        private SourceSyncPolicy         $policy = SourceSyncPolicy::CACHE_ASIDE,
        private DeferredSourceWrite|null $deferredWrite = null
    ) {}

    public function write(CacheKey $key, mixed $value) : void
    {
        match ($this->policy) {
            SourceSyncPolicy::WRITE_THROUGH => $this->writeThrough(key: $key, value: $value),
            SourceSyncPolicy::WRITE_AROUND  => $this->writeAround(key: $key, value: $value),
            SourceSyncPolicy::WRITE_BEHIND  => $this->writeBehind(key: $key, value: $value),
            SourceSyncPolicy::CACHE_ASIDE   => $this->cacheAsideWrite(key: $key, value: $value),
            SourceSyncPolicy::NO_SYNC       => $this->writeToSourceOnly(key: $key, value: $value),
        };
    }

    private function writeThrough(CacheKey $key, mixed $value) : void
    {
        $writer = new WriteValueToSource(source: $this->source);
        $writer->write(key: $key, value: $value);

        if ($this->cache !== null) {
            $this->invalidateCache(key: $key);
        }
    }

    private function invalidateCache(CacheKey $key) : void
    {
        $this->cache?->forget(key: $key);
    }

    private function writeAround(CacheKey $key, mixed $value) : void
    {
        $writer = new WriteValueToSource(source: $this->source);
        $writer->write(key: $key, value: $value);

        $this->invalidateCache(key: $key);
    }

    private function writeBehind(CacheKey $key, mixed $value) : void
    {
        $this->invalidateCache(key: $key);

        if ($this->deferredWrite === null) {
            $this->deferredWrite = new DeferredSourceWrite(source: $this->source);
        }

        $this->deferredWrite->queueFromCacheKey(key: $key, value: $value);
    }

    private function cacheAsideWrite(CacheKey $key, mixed $value) : void
    {
        $writer = new WriteValueToSource(source: $this->source);
        $writer->write(key: $key, value: $value);
    }

    private function writeToSourceOnly(CacheKey $key, mixed $value) : void {}

    public function delete(CacheKey $key) : void
    {
        match ($this->policy) {
            SourceSyncPolicy::WRITE_THROUGH => $this->deleteThrough(key: $key),
            SourceSyncPolicy::WRITE_AROUND,
            SourceSyncPolicy::CACHE_ASIDE   => $this->invalidateCache(key: $key),
            SourceSyncPolicy::WRITE_BEHIND  => $this->invalidateCache(key: $key),
            SourceSyncPolicy::NO_SYNC       => $this->deleteFromSource(key: $key),
        };
    }

    private function deleteThrough(CacheKey $key) : void
    {
        $deleter = new DeleteValueFromSource(source: $this->source);
        $deleter->delete(key: $key);

        $this->invalidateCache(key: $key);
    }

    private function deleteFromSource(CacheKey $key) : void
    {
        $deleter = new DeleteValueFromSource(source: $this->source);
        $deleter->delete(key: $key);
    }

    public function loadFromSource(CacheKey $key) : mixed
    {
        $sourceKey = CacheSourceKey::create(
            key      : $key->fullKey(),
            namespace: $key->namespace
        );

        return $this->source->load(key: $sourceKey);
    }

    public function shouldPopulateCacheOnMiss() : bool
    {
        return match ($this->policy) {
            SourceSyncPolicy::CACHE_ASIDE,
            SourceSyncPolicy::WRITE_THROUGH,
            SourceSyncPolicy::WRITE_BEHIND => true,
            SourceSyncPolicy::NO_SYNC,
            SourceSyncPolicy::WRITE_AROUND => false,
        };
    }

    public function flushPendingWrites() : int
    {
        return $this->deferredWrite?->flush() ?? 0;
    }

    public function pendingWritesCount() : int
    {
        return $this->deferredWrite?->pendingCount() ?? 0;
    }
}