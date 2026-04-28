<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Foundation\Time\Clock;
use Throwable;

final class FallbackCacheStore implements CacheStore
{
    public function __construct(
        private CacheStore $primary,
        private CacheStore $fallback
    ) {}

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        try {
            $result = $this->primary->read(key: $key, clock: $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                return $result;
            }
        } catch (Throwable) {
        }

        try {
            $result = $this->fallback->read(key: $key, clock: $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->primary->write(key: $key, record: $result->record);
            }

            return $result;
        } catch (Throwable) {
            return new CacheStoreRecordWasMissing(key: $key);
        }
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        try {
            $this->primary->write(key: $key, record: $record);
        } catch (Throwable) {
            $this->fallback->write(key: $key, record: $record);
        }
    }

    public function forget(CacheKey $key) : void
    {
        try {
            $this->primary->forget(key: $key);
        } catch (Throwable) {
        }

        try {
            $this->fallback->forget(key: $key);
        } catch (Throwable) {
        }
    }

    public function clear() : void
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

    public function exists(CacheKey $key) : bool
    {
        try {
            return $this->primary->exists(key: $key);
        } catch (Throwable) {
            return $this->fallback->exists(key: $key);
        }
    }
}