<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Distribution\UseCacheTiers;

use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use components\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use components\Cache\System\Foundation\Time\Clock;

final readonly class PromoteCachedValueToFasterTier
{
    public function __construct(
        private TieredCache $tieredCache,
        private Clock       $clock
    ) {}

    public function promote(CacheKey $key, StoredCacheRecord|null $record = null) : bool
    {
        if ($record === null) {
            foreach ($this->tieredCache->tiers as $tier => $store) {
                if ($store === null) {
                    continue;
                }

                $result = $store->read(key: $key, clock: $this->clock);

                if ($result instanceof CacheStoreRecordWasFound) {
                    $record = $result->record;
                    break;
                }
            }

            if ($record === null) {
                return false;
            }
        }

        $this->tieredCache->forget(key: $key);
        $this->tieredCache->write(key: $key, record: $record);

        return true;
    }
}