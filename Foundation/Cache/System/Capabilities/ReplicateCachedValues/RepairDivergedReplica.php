<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ReplicateCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class RepairDivergedReplica
{
    /** @var array<CacheStore> */
    private array $stores;

    public function __construct(
        private Clock $clock,
        CacheStore    ...$stores
    )
    {
        $this->stores = $stores;
    }

    public function repairAll(int $referenceReplicaIndex = 0) : int
    {
        $repaired = 0;

        foreach ($this->stores[$referenceReplicaIndex] ?? [] as $key => $record) {
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create($key);

            if ($this->repair($cacheKey, $referenceReplicaIndex)) {
                $repaired++;
            }
        }

        return $repaired;
    }

    public function repair(CacheKey $key, int $referenceReplicaIndex = 0) : bool
    {
        if (! isset($this->stores[$referenceReplicaIndex])) {
            return false;
        }

        $referenceStore  = $this->stores[$referenceReplicaIndex];
        $referenceResult = $referenceStore->read($key, $this->clock);

        if (! $referenceResult instanceof CacheStoreRecordWasFound) {
            return false;
        }

        $referenceRecord = $referenceResult->record;
        $repaired        = false;

        foreach ($this->stores as $index => $store) {
            if ($index === $referenceReplicaIndex) {
                continue;
            }

            try {
                $result = $store->read($key, $this->clock);

                if ($result instanceof CacheStoreRecordWasMissing) {
                    $store->write($key, $referenceRecord);
                    $repaired = true;
                } elseif ($result->record !== $referenceRecord) {
                    $store->write($key, $referenceRecord);
                    $repaired = true;
                }
            } catch (Throwable) {
            }
        }

        return $repaired;
    }
}