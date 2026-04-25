<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
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
            $result = $this->primary->read($key, $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                return $result;
            }
        } catch (Throwable) {
        }

        try {
            $result = $this->fallback->read($key, $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->primary->write($key, $result->record);
            }

            return $result;
        } catch (Throwable) {
            return new CacheStoreRecordWasMissing($key);
        }
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        try {
            $this->primary->write($key, $record);
        } catch (Throwable) {
            $this->fallback->write($key, $record);
        }
    }

    public function forget(CacheKey $key) : void
    {
        try {
            $this->primary->forget($key);
        } catch (Throwable) {
        }

        try {
            $this->fallback->forget($key);
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
            return $this->primary->exists($key);
        } catch (Throwable) {
            return $this->fallback->exists($key);
        }
    }
}