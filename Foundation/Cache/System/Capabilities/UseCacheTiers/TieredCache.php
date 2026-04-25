<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\UseCacheTiers;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;

final class TieredCache implements CacheStore
{
    /** @var array<CacheTier, CacheStore> */
    private array $tiers            = [];
    private int   $currentTierIndex = 0;

    public function __construct(
        private Clock $clock,
        CacheTier     ...$tierDefinitions
    )
    {
        foreach ($tierDefinitions as $tier) {
            $this->tiers[$tier] = null;
        }
    }

    public function registerTier(CacheTier $tier, CacheStore $store) : self
    {
        $this->tiers[$tier] = $store;

        return $this;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $this->rewindTiers();

        while ( $this->currentTierIndex < count($this->tiers) ) {
            $tier  = $this->getCurrentTier();
            $store = $this->getStoreForTier($tier);

            if ($store === null) {
                $this->advanceTier();
                continue;
            }

            $result = $store->read($key, $clock);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->promoteToFasterTier($key, $result->record);

                return $result;
            }

            $this->advanceTier();
        }

        return new CacheStoreRecordWasMissing($key);
    }

    private function rewindTiers() : void
    {
        $this->currentTierIndex = 0;
    }

    private function getCurrentTier() : CacheTier
    {
        $tiers = array_keys($this->tiers);

        return $tiers[$this->currentTierIndex] ?? $this->tiers[array_key_first($this->tiers)];
    }

    private function getStoreForTier(CacheTier $tier) : ?CacheStore
    {
        return $this->tiers[$tier] ?? null;
    }

    private function advanceTier() : void
    {
        $this->currentTierIndex++;
    }

    private function promoteToFasterTier(CacheKey $key, StoredCacheRecord $record) : void
    {
        $currentTier = $this->getCurrentTier();

        foreach ($this->tiers as $tier => $store) {
            if ($tier->isFasterThan($currentTier) && $store !== null) {
                if (! $store->exists($key)) {
                    $store->write($key, $record);
                }
            }
        }
    }

    public function exists(CacheKey $key) : bool
    {
        foreach ($this->tiers as $store) {
            if ($store !== null && $store->exists($key)) {
                return true;
            }
        }

        return false;
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        foreach ($this->tiers as $tier => $store) {
            if ($store !== null && $tier->canStore($this->getTierSize($tier))) {
                $store->write($key, $record);
            }
        }
    }

    private function getTierSize(CacheTier $tier) : int
    {
        return 0;
    }

    public function forget(CacheKey $key) : void
    {
        foreach ($this->tiers as $store) {
            if ($store !== null) {
                $store->forget($key);
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

    public function getTier(CacheTierName $name) : ?CacheStore
    {
        foreach ($this->tiers as $tier => $store) {
            if ($tier->name === $name) {
                return $store;
            }
        }

        return null;
    }
}