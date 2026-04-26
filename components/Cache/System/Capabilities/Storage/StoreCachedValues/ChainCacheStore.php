<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Storage\StoreCachedValues;

use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use components\Cache\System\Foundation\Time\Clock;
use InvalidArgumentException;

final class ChainCacheStore implements CacheStore
{
    /** @var CacheStore[] */
    private array $stores;

    public function __construct(
        CacheStore ...$stores
    )
    {
        if (count($stores) === 0) {
            throw new InvalidArgumentException(message: 'At least one cache store must be provided');
        }

        $this->stores = $stores;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        foreach ($this->stores as $store) {
            $result = $store->read(key: $key, clock: $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->propagateToLowerTiers(key: $key, record: $result->record, clock: $clock);

                return $result;
            }
        }

        return new CacheStoreRecordWasMissing(key: $key);
    }

    private function propagateToLowerTiers(CacheKey $key, StoredCacheRecord $record, Clock $clock) : void
    {
        $found = false;

        foreach ($this->stores as $index => $store) {
            if ($found) {
                $store->write(key: $key, record: $record);
            }

            if ($store->exists(key: $key)) {
                $found = true;
            }
        }
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        foreach ($this->stores as $store) {
            $store->write(key: $key, record: $record);
        }
    }

    public function exists(CacheKey $key) : bool
    {
        foreach ($this->stores as $store) {
            if ($store->exists(key: $key)) {
                return true;
            }
        }

        return false;
    }

    public function forget(CacheKey $key) : void
    {
        foreach ($this->stores as $store) {
            $store->forget(key: $key);
        }
    }

    public function clear() : void
    {
        foreach ($this->stores as $store) {
            $store->clear();
        }
    }
}