<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
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
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create(key: $key);

            if ($this->repair(key: $cacheKey, referenceReplicaIndex: $referenceReplicaIndex)) {
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
        $referenceResult = $referenceStore->read(key: $key, clock: $this->clock);

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
                $result = $store->read(key: $key, clock: $this->clock);

                if ($result instanceof CacheStoreRecordWasMissing) {
                    $store->write(key: $key, record: $referenceRecord);
                    $repaired = true;
                } elseif ($result->record !== $referenceRecord) {
                    $store->write(key: $key, record: $referenceRecord);
                    $repaired = true;
                }
            } catch (Throwable) {
            }
        }

        return $repaired;
    }
}