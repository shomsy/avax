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
use Override;

final class TieredCache implements CacheStore
{
    /** @var array<string, CacheStore|null> */
    private array $tiers = [];

    /** @var array<string, CacheTier> */
    private array $tierDefinitions = [];

    /** @var array<string> */
    private array $tierOrder = [];

    private int $currentTierIndex = 0;

    public function __construct(
        private readonly Clock $clock,
        CacheTier ...$cacheTier,
    )
    {
        foreach ($cacheTier as $tier) {
            $key               = $tier->cacheTierName->value;
            $this->tierDefinitions[$key] = $tier;
            $this->tiers[$key] = null;
            $this->tierOrder[] = $key;
        }
    }

    public function registerTier(CacheTier $cacheTier, CacheStore $cacheStore) : self
    {
        $key               = $cacheTier->cacheTierName->value;
        $this->tierDefinitions[$key] = $cacheTier;
        $this->tiers[$key] = $cacheStore;

        return $this;
    }

    /**
     * @return array<string, CacheStore|null>
     */
    public function stores() : array
    {
        return $this->tiers;
    }

    public function clock() : Clock
    {
        return $this->clock;
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $this->rewindTiers();

        while ( $this->currentTierIndex < count($this->tierOrder) ) {
            $tierName = $this->getCurrentTierName();

            if ($tierName === null) {
                $this->advanceTier();

                continue;
            }

            $store = $this->getStoreForTier(tierName: $tierName);

            if (! $store instanceof CacheStore) {
                $this->advanceTier();

                continue;
            }

            $result = $store->read(clock: $clock, key: $cacheKey);

            if ($result instanceof CacheStoreRecordWasFound) {
                $this->promoteToFasterTier(key: $cacheKey, record: $result->record);

                return $result;
            }

            $this->advanceTier();
        }

        return new CacheStoreRecordWasMissing(key: $cacheKey);
    }

    private function rewindTiers() : void
    {
        $this->currentTierIndex = 0;
    }

    private function getCurrentTierName() : ?string
    {
        return $this->tierOrder[$this->currentTierIndex] ?? null;
    }

    private function advanceTier() : void
    {
        $this->currentTierIndex++;
    }

    private function getStoreForTier(string $tierName) : ?CacheStore
    {
        return $this->tiers[$tierName] ?? null;
    }

    private function promoteToFasterTier(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void
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
            if (! $tier->isFasterThan(other: $currentTier)) {
                continue;
            }

            if ($this->tiers[$tierName] === null) {
                continue;
            }

            if ($this->tiers[$tierName]->exists(key: $cacheKey)) {
                continue;
            }

            $this->tiers[$tierName]->write(key: $cacheKey, record: $storedCacheRecord);
        }
    }

    #[Override]
    public function exists(CacheKey $cacheKey) : bool
    {
        return array_any($this->tiers, fn ($tier) : bool => $tier !== null && $tier->exists(key: $cacheKey));
    }

    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void
    {
        foreach ($this->tiers as $tierName => $store) {
            if ($store !== null) {
                $tier = $this->tierDefinitions[$tierName];
                if ($tier->canStore(currentSize: $this->getTierSize(tierName: $tierName))) {
                    $store->write(key: $cacheKey, record: $storedCacheRecord);
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

    #[Override]
    public function forget(CacheKey $cacheKey) : void
    {
        foreach ($this->tiers as $tier) {
            if ($tier !== null) {
                $tier->forget(key: $cacheKey);
            }
        }
    }

    #[Override]
    public function clear() : void
    {
        foreach ($this->tiers as $tier) {
            if ($tier !== null) {
                $tier->clear();
            }
        }
    }

    public function getTier(CacheTierName $cacheTierName) : ?CacheStore
    {
        return $this->tiers[$cacheTierName->value] ?? null;
    }
}
