<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Override;
use Throwable;

final readonly class FallbackCacheStore implements CacheStore
{
    public function __construct(
        private CacheStore $primary,
        private CacheStore $fallback,
    ) {
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        try {
            $result = $this->primary->read(cacheKey: $cacheKey, clock: $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                return $result;
            }
        } catch (Throwable) {
        }

        try {
            $result = $this->fallback->read(cacheKey: $cacheKey, clock: $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->primary->write(cacheKey: $cacheKey, storedCacheRecord: $result->storedCacheRecord);
            }

            return $result;
        } catch (Throwable) {
            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }
    }

    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void
    {
        try {
            $this->primary->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
        } catch (Throwable) {
            $this->fallback->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
        }
    }

    #[Override]
    public function forget(CacheKey $cacheKey): void
    {
        try {
            $this->primary->forget(cacheKey: $cacheKey);
        } catch (Throwable) {
        }

        try {
            $this->fallback->forget(cacheKey: $cacheKey);
        } catch (Throwable) {
        }
    }

    #[Override]
    public function clear(): void
    {
        try {
            $this->primary->clear();
        } catch (Throwable) {
        }

        try {
            $this->fallback->clear();
        } catch (Throwable) {
        }
    }

    #[Override]
    public function exists(CacheKey $cacheKey): bool
    {
        try {
            return $this->primary->exists(cacheKey: $cacheKey);
        } catch (Throwable) {
            return $this->fallback->exists(cacheKey: $cacheKey);
        }
    }
}
