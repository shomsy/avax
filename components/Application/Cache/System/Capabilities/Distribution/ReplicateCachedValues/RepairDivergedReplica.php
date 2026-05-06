<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class RepairDivergedReplica
{
    /** @var list<CacheStore> */
    private array $stores;

    public function __construct(
        private Clock $clock,
        CacheStore ...$cacheStore,
    )
    {
        $this->stores = $cacheStore;
    }

    public function repairAll(int $referenceReplicaIndex = 0) : int
    {
        $repaired = 0;

        $referenceStore = $this->stores[$referenceReplicaIndex] ?? null;

        if (! $referenceStore instanceof InMemoryCacheStore) {
            return 0;
        }

        foreach ($referenceStore->getAllKeys() as $key) {
            $cacheKey = CacheKey::create(key: $key);

            if ($this->repair(cacheKey: $cacheKey, referenceReplicaIndex: $referenceReplicaIndex)) {
                $repaired++;
            }
        }

        return $repaired;
    }

    public function repair(CacheKey $cacheKey, int $referenceReplicaIndex = 0) : bool
    {
        if (! isset($this->stores[$referenceReplicaIndex])) {
            return false;
        }

        $referenceStore = $this->stores[$referenceReplicaIndex];
        $referenceResult = $referenceStore->read(cacheKey: $cacheKey, clock: $this->clock);

        if (! $referenceResult instanceof CacheStoreRecordWasFound) {
            return false;
        }

        $referenceRecord = $referenceResult->storedCacheRecord;
        $repaired       = false;

        foreach ($this->stores as $index => $store) {
            if ($index === $referenceReplicaIndex) {
                continue;
            }

            try {
                $result = $store->read(cacheKey: $cacheKey, clock: $this->clock);

                if ($result instanceof CacheStoreRecordWasMissing) {
                    $store->write(cacheKey: $cacheKey, storedCacheRecord: $referenceRecord);
                    $repaired = true;
                } elseif ($result->storedCacheRecord !== $referenceRecord) {
                    $store->write(cacheKey: $cacheKey, storedCacheRecord: $referenceRecord);
                    $repaired = true;
                }
            } catch (Throwable) {
            }
        }

        return $repaired;
    }
}
