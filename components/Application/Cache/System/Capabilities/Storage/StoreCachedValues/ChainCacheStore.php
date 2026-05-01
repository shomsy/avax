<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use InvalidArgumentException;
use Override;

final readonly class ChainCacheStore implements CacheStore
{
    /** @var CacheStore[] */
    private array $stores;

    public function __construct(
        CacheStore ...$cacheStore,
    )
    {
        if ($cacheStore === []) {
            throw new InvalidArgumentException(message: 'At least one cache store must be provided');
        }

        $this->stores = $cacheStore;
    }

    #[Override]
    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        foreach ($this->stores as $store) {
            $result = $store->read(key: $key, clock: $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->propagateToLowerTiers(key: $key, record: $result->record);

                return $result;
            }
        }

        return new CacheStoreRecordWasMissing(key: $key);
    }

    private function propagateToLowerTiers(CacheKey $key, StoredCacheRecord $record) : void
    {
        $found = false;

        foreach ($this->stores as $store) {
            if ($found) {
                $store->write(key: $key, record: $record);
            }

            if ($store->exists(key: $key)) {
                $found = true;
            }
        }
    }

    #[Override]
    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        foreach ($this->stores as $store) {
            $store->write(key: $key, record: $record);
        }
    }

    #[Override]
    public function exists(CacheKey $key) : bool
    {
        foreach ($this->stores as $store) {
            if ($store->exists(key: $key)) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function forget(CacheKey $key) : void
    {
        foreach ($this->stores as $store) {
            $store->forget(key: $key);
        }
    }

    #[Override]
    public function clear() : void
    {
        foreach ($this->stores as $store) {
            $store->clear();
        }
    }
}
