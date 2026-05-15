<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class ReadCachedValueFromReplica
{
    /** @var list<CacheStore> */
    private array $stores;

    public function __construct(
        private ChooseReplicaForRead $chooseReplicaForRead,
        private Clock $clock,
        CacheStore ...$cacheStore,
    ) {
        $this->stores = $cacheStore;
    }

    public function read(
        CacheKey $cacheKey,
        ReplicationPolicy $replicationPolicy = ReplicationPolicy::SYNCHRONOUS,
    ): CacheStoreRecordWasFound|CacheStoreRecordWasMissing {
        return match ($replicationPolicy) {
            ReplicationPolicy::SYNCHRONOUS => $this->readFromPrimary(cacheKey: $cacheKey),
            ReplicationPolicy::ASYNCHRONOUS => $this->readFromClosest(cacheKey: $cacheKey),
            ReplicationPolicy::QUORUM => $this->readWithQuorum(cacheKey: $cacheKey),
        };
    }

    private function readFromPrimary(CacheKey $cacheKey): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        return $this->stores[0]->read(cacheKey: $cacheKey, clock: $this->clock);
    }

    private function readFromClosest(CacheKey $cacheKey): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        foreach ($this->stores as $store) {
            $result = $store->read(cacheKey: $cacheKey, clock: $this->clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                return $result;
            }
        }

        return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
    }

    private function readWithQuorum(CacheKey $cacheKey): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $results = [];
        $firstFound = null;

        foreach ($this->stores as $store) {
            try {
                $result = $store->read(cacheKey: $cacheKey, clock: $this->clock);

                if ($result instanceof CacheStoreRecordWasFound) {
                    $results[] = $result;

                    if ($firstFound === null) {
                        $firstFound = $result;
                    }
                }
            } catch (Throwable) {
            }
        }

        if (count($results) >= $this->chooseReplicaForRead->replicaCount()->quorumSize()) {
            if ($firstFound !== null) {
                return $firstFound;
            }

            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }

        return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
    }
}
