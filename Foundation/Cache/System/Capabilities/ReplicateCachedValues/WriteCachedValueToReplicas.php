<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ReplicateCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use InvalidArgumentException;
use Throwable;

final readonly class WriteCachedValueToReplicas
{
    /** @var array<CacheStore> */
    private array $stores;

    public function __construct(
        ReplicaCount $replicaCount,
        CacheStore   ...$stores
    )
    {
        if (count($stores) < $replicaCount->totalReplicas()) {
            throw new InvalidArgumentException(
                sprintf('Expected %d stores, got %d', $replicaCount->totalReplicas(), count($stores))
            );
        }

        $this->stores = $stores;
    }

    public function writePrimary(CacheKey $key, StoredCacheRecord $record) : void
    {
        $this->stores[0]->write($key, $record);
    }

    public function writeAll(CacheKey $key, StoredCacheRecord $record, ReplicationPolicy $policy) : void
    {
        match ($policy) {
            ReplicationPolicy::SYNCHRONOUS  => $this->writeSynchronously($key, $record),
            ReplicationPolicy::ASYNCHRONOUS => $this->writeAsynchronously($key, $record),
            ReplicationPolicy::QUORUM       => $this->writeWithQuorum($key, $record),
        };
    }

    private function writeSynchronously(CacheKey $key, StoredCacheRecord $record) : void
    {
        foreach ($this->stores as $store) {
            $store->write($key, $record);
        }
    }

    private function writeAsynchronously(CacheKey $key, StoredCacheRecord $record) : void
    {
        $this->stores[0]->write($key, $record);
    }

    private function writeWithQuorum(CacheKey $key, StoredCacheRecord $record) : void
    {
        $replicaCount = new ReplicaCount(
            primary    : 1,
            secondaries: count($this->stores) - 1
        );
        $quorumSize   = $replicaCount->quorumSize();
        $written      = 0;

        foreach ($this->stores as $store) {
            try {
                $store->write($key, $record);
                $written++;

                if ($written >= $quorumSize) {
                    return;
                }
            } catch (Throwable) {
            }
        }
    }
}