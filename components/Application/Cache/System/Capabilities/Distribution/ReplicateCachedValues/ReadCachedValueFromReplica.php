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
    /** @var array<CacheStore> */
    private array $stores;

    public function __construct(
        private ChooseReplicaForRead $selector,
        private Clock                $clock,
        CacheStore                   ...$stores
    )
    {
        $this->stores = $stores;
    }

    public function read(
        CacheKey          $key,
        ReplicationPolicy $policy = ReplicationPolicy::SYNCHRONOUS
    ) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        return match ($policy) {
            ReplicationPolicy::SYNCHRONOUS  => $this->readFromPrimary(key: $key),
            ReplicationPolicy::ASYNCHRONOUS => $this->readFromClosest(key: $key),
            ReplicationPolicy::QUORUM       => $this->readWithQuorum(key: $key),
        };
    }

    private function readFromPrimary(CacheKey $key) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        return $this->stores[0]->read(key: $key, clock: $this->clock);
    }

    private function readFromClosest(CacheKey $key) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        foreach ($this->stores as $store) {
            $result = $store->read(key: $key, clock: $this->clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                return $result;
            }
        }

        return new CacheStoreRecordWasMissing(key: $key);
    }

    private function readWithQuorum(CacheKey $key) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $results = [];
        $found   = null;

        foreach ($this->stores as $store) {
            try {
                $result = $store->read(key: $key, clock: $this->clock);

                if ($result instanceof CacheStoreRecordWasFound) {
                    $results[] = $result;
                    $found     ??= $result;
                }
            } catch (Throwable) {
            }
        }

        if (count($results) >= $this->selector->getAllReplicas()->quorumSize()) {
            return $found ?? new CacheStoreRecordWasMissing(key: $key);
        }

        return new CacheStoreRecordWasMissing(key: $key);
    }
}