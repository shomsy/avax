<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use InvalidArgumentException;
use Throwable;

final readonly class WriteCachedValueToReplicas
{
    /** @var list<CacheStore> */
    private array $stores;

    public function __construct(
        ReplicaCount $replicaCount,
        CacheStore ...$cacheStore,
    )
    {
        if (count($cacheStore) < $replicaCount->totalReplicas()) {
            throw new InvalidArgumentException(
                message: sprintf('Expected %d stores, got %d', $replicaCount->totalReplicas(), count($cacheStore)),
            );
        }

        $this->stores = $cacheStore;
    }

    public function writePrimary(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void
    {
        $this->stores[0]->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
    }

    public function writeAll(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord, ReplicationPolicy $replicationPolicy) : void
    {
        match ($replicationPolicy) {
            ReplicationPolicy::SYNCHRONOUS => $this->writeSynchronously(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord),
            ReplicationPolicy::ASYNCHRONOUS => $this->writeAsynchronously(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord),
            ReplicationPolicy::QUORUM      => $this->writeWithQuorum(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord),
        };
    }

    private function writeSynchronously(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void
    {
        foreach ($this->stores as $store) {
            $store->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
        }
    }

    private function writeAsynchronously(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void
    {
        $this->stores[0]->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
    }

    private function writeWithQuorum(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void
    {
        $replicaCount = new ReplicaCount(
            primary    : 1,
            secondaries: count($this->stores) - 1,
        );
        $quorumSize = $replicaCount->quorumSize();
        $written = 0;

        foreach ($this->stores as $store) {
            try {
                $store->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
                $written++;

                if ($written >= $quorumSize) {
                    return;
                }
            } catch (Throwable) {
            }
        }
    }
}
