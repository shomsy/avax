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
    ) {
        if ($cacheStore === []) {
            throw new InvalidArgumentException(message: 'At least one cache store must be provided');
        }

        $this->stores = $cacheStore;
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        foreach ($this->stores as $store) {
            $result = $store->read(clock: $clock, key: $cacheKey);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->propagateToLowerTiers(key: $cacheKey, record: $result->record);

                return $result;
            }
        }

        return new CacheStoreRecordWasMissing(key: $cacheKey);
    }

    private function propagateToLowerTiers(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void
    {
        $found = false;

        foreach ($this->stores as $store) {
            if ($found) {
                $store->write(key: $cacheKey, record: $storedCacheRecord);
            }

            if ($store->exists(key: $cacheKey)) {
                $found = true;
            }
        }
    }

    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void
    {
        foreach ($this->stores as $store) {
            $store->write(key: $cacheKey, record: $storedCacheRecord);
        }
    }

    #[Override]
    public function exists(CacheKey $cacheKey): bool
    {
        return array_any($this->stores, fn ($store) => $store->exists(key: $cacheKey));
    }

    #[Override]
    public function forget(CacheKey $cacheKey): void
    {
        foreach ($this->stores as $store) {
            $store->forget(key: $cacheKey);
        }
    }

    #[Override]
    public function clear(): void
    {
        foreach ($this->stores as $store) {
            $store->clear();
        }
    }
}
