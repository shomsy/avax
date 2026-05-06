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
    public function __construct(private readonly CacheSource $cacheSource, private readonly ?CacheStore $cacheStore = null, private readonly SourceSyncPolicy $sourceSyncPolicy = SourceSyncPolicy::CACHE_ASIDE, private ?DeferredSourceWrite $deferredSourceWrite = null)
    {
    }

    public function write(CacheKey $cacheKey, mixed $value): void
    {
        match ($this->sourceSyncPolicy) {
            SourceSyncPolicy::WRITE_THROUGH => $this->writeThrough(cacheKey: $cacheKey, value: $value),
            SourceSyncPolicy::WRITE_AROUND => $this->writeAround(cacheKey: $cacheKey, value: $value),
            SourceSyncPolicy::WRITE_BEHIND => $this->writeBehind(cacheKey: $cacheKey, value: $value),
            SourceSyncPolicy::CACHE_ASIDE => $this->cacheAsideWrite(cacheKey: $cacheKey, value: $value),
            SourceSyncPolicy::NO_SYNC => $this->writeToSourceOnly(),
        };
    }

    private function writeThrough(CacheKey $cacheKey, mixed $value): void
    {
        $writeValueToSource = new WriteValueToSource(cacheSource: $this->cacheSource);
        $writeValueToSource->write(cacheKey: $cacheKey, value: $value);

        if ($this->cacheStore instanceof CacheStore) {
            $this->invalidateCache(cacheKey: $cacheKey);
        }
    }

    private function invalidateCache(CacheKey $cacheKey): void
    {
        $this->cacheStore?->forget(cacheKey: $cacheKey);
    }

    private function writeAround(CacheKey $cacheKey, mixed $value): void
    {
        $writeValueToSource = new WriteValueToSource(cacheSource: $this->cacheSource);
        $writeValueToSource->write(cacheKey: $cacheKey, value: $value);

        $this->invalidateCache(cacheKey: $cacheKey);
    }

    private function writeBehind(CacheKey $cacheKey, mixed $value): void
    {
        $this->invalidateCache(cacheKey: $cacheKey);

        if (! $this->deferredSourceWrite instanceof DeferredSourceWrite) {
            $this->deferredSourceWrite = new DeferredSourceWrite(cacheSource: $this->cacheSource);
        }

        $this->deferredSourceWrite->queueFromCacheKey(cacheKey: $cacheKey, value: $value);
    }

    private function cacheAsideWrite(CacheKey $cacheKey, mixed $value): void
    {
        $writeValueToSource = new WriteValueToSource(cacheSource: $this->cacheSource);
        $writeValueToSource->write(cacheKey: $cacheKey, value: $value);
    }

    private function writeToSourceOnly(): void
    {
    }

    public function delete(CacheKey $cacheKey): void
    {
        match ($this->sourceSyncPolicy) {
            SourceSyncPolicy::WRITE_THROUGH => $this->deleteThrough(cacheKey: $cacheKey),
            SourceSyncPolicy::WRITE_AROUND,
            SourceSyncPolicy::CACHE_ASIDE => $this->invalidateCache(cacheKey: $cacheKey),
            SourceSyncPolicy::WRITE_BEHIND => $this->invalidateCache(cacheKey: $cacheKey),
            SourceSyncPolicy::NO_SYNC => $this->deleteFromSource(cacheKey: $cacheKey),
        };
    }

    private function deleteThrough(CacheKey $cacheKey): void
    {
        $deleteValueFromSource = new DeleteValueFromSource(cacheSource: $this->cacheSource);
        $deleteValueFromSource->delete(cacheKey: $cacheKey);

        $this->invalidateCache(cacheKey: $cacheKey);
    }

    private function deleteFromSource(CacheKey $cacheKey): void
    {
        $deleteValueFromSource = new DeleteValueFromSource(cacheSource: $this->cacheSource);
        $deleteValueFromSource->delete(cacheKey: $cacheKey);
    }

    public function loadFromSource(CacheKey $cacheKey): mixed
    {
        $cacheSourceKey = CacheSourceKey::create(
            key      : $cacheKey->fullKey(),
            namespace: $cacheKey->namespace,
        );

        return $this->cacheSource->load($cacheSourceKey);
    }

    public function shouldPopulateCacheOnMiss(): bool
    {
        return match ($this->sourceSyncPolicy) {
            SourceSyncPolicy::CACHE_ASIDE,
            SourceSyncPolicy::WRITE_THROUGH,
            SourceSyncPolicy::WRITE_BEHIND => true,
            SourceSyncPolicy::NO_SYNC,
            SourceSyncPolicy::WRITE_AROUND => false,
        };
    }

    public function flushPendingWrites(): int
    {
        return $this->deferredSourceWrite?->flush() ?? 0;
    }

    public function pendingWritesCount(): int
    {
        return $this->deferredSourceWrite?->pendingCount() ?? 0;
    }
}
