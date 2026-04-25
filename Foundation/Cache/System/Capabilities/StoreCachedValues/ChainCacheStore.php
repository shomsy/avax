<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Foundation\Time\Clock;
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
            throw new InvalidArgumentException('At least one cache store must be provided');
        }

        $this->stores = $stores;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        foreach ($this->stores as $store) {
            $result = $store->read($key, $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->propagateToLowerTiers($key, $result->record, $clock);

                return $result;
            }
        }

        return new CacheStoreRecordWasMissing($key);
    }

    private function propagateToLowerTiers(CacheKey $key, StoredCacheRecord $record, Clock $clock) : void
    {
        $found = false;

        foreach ($this->stores as $index => $store) {
            if ($found) {
                $store->write($key, $record);
            }

            if ($store->exists($key)) {
                $found = true;
            }
        }
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        foreach ($this->stores as $store) {
            $store->write($key, $record);
        }
    }

    public function exists(CacheKey $key) : bool
    {
        foreach ($this->stores as $store) {
            if ($store->exists($key)) {
                return true;
            }
        }

        return false;
    }

    public function forget(CacheKey $key) : void
    {
        foreach ($this->stores as $store) {
            $store->forget($key);
        }
    }

    public function clear() : void
    {
        foreach ($this->stores as $store) {
            $store->clear();
        }
    }
}