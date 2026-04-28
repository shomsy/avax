<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final class TieredCache implements CacheStore
{
    /** @var array<string, CacheStore> */
    private array $tiers = [];
    /** @var array<string, CacheTier> */
    private array $tierDefinitions = [];
    /** @var array<string> */
    private array $tierOrder        = [];
    private int   $currentTierIndex = 0;

    public function __construct(
        private Clock $clock,
        CacheTier     ...$tierDefinitions
    )
    {
        foreach ($tierDefinitions as $tier) {
            $key                         = $tier->name->value;
            $this->tierDefinitions[$key] = $tier;
            $this->tiers[$key]           = null;
            $this->tierOrder[]           = $key;
        }
    }

    public function registerTier(CacheTier $tier, CacheStore $store) : self
    {
        $key                         = $tier->name->value;
        $this->tierDefinitions[$key] = $tier;
        $this->tiers[$key]           = $store;

        return $this;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $this->rewindTiers();

        while ( $this->currentTierIndex < count($this->tierOrder) ) {
            $tierName = $this->getCurrentTierName();

            if ($tierName === null) {
                $this->advanceTier();
                continue;
            }

            $store = $this->getStoreForTier(tierName: $tierName);

            if ($store === null) {
                $this->advanceTier();
                continue;
            }

            $result = $store->read(key: $key, clock: $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->promoteToFasterTier(key: $key, record: $result->record);

                return $result;
            }

            $this->advanceTier();
        }

        return new CacheStoreRecordWasMissing(key: $key);
    }

    private function rewindTiers() : void
    {
        $this->currentTierIndex = 0;
    }

    private function getCurrentTierName() : string|null
    {
        return $this->tierOrder[$this->currentTierIndex] ?? null;
    }

    private function advanceTier() : void
    {
        $this->currentTierIndex++;
    }

    private function getStoreForTier(string $tierName) : CacheStore|null
    {
        return $this->tiers[$tierName] ?? null;
    }

    private function promoteToFasterTier(CacheKey $key, StoredCacheRecord $record) : void
    {
        $currentTierName = $this->getCurrentTierName();

        if ($currentTierName === null) {
            return;
        }

        $currentTier = $this->tierDefinitions[$currentTierName] ?? null;

        if ($currentTier === null) {
            return;
        }

        foreach ($this->tierDefinitions as $tierName => $tier) {
            if ($tier->isFasterThan(other: $currentTier) && $this->tiers[$tierName] !== null) {
                if (! $this->tiers[$tierName]->exists(key: $key)) {
                    $this->tiers[$tierName]->write(key: $key, record: $record);
                }
            }
        }
    }

    public function exists(CacheKey $key) : bool
    {
        foreach ($this->tiers as $store) {
            if ($store !== null && $store->exists(key: $key)) {
                return true;
            }
        }

        return false;
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        foreach ($this->tiers as $tierName => $store) {
            if ($store !== null) {
                $tier = $this->tierDefinitions[$tierName];
                if ($tier->canStore(currentSize: $this->getTierSize(tierName: $tierName))) {
                    $store->write(key: $key, record: $record);
                }
            }
        }
    }

    private function getTierSize(string $tierName) : int
    {
        $store = $this->tiers[$tierName] ?? null;

        if ($store instanceof InMemoryCacheStore) {
            return $store->count();
        }

        return 0;
    }

    public function forget(CacheKey $key) : void
    {
        foreach ($this->tiers as $store) {
            if ($store !== null) {
                $store->forget(key: $key);
            }
        }
    }

    public function clear() : void
    {
        foreach ($this->tiers as $store) {
            if ($store !== null) {
                $store->clear();
            }
        }
    }

    public function getTier(CacheTierName $name) : CacheStore|null
    {
        return $this->tiers[$name->value] ?? null;
    }
}