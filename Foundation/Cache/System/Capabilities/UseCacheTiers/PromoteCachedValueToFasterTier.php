<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\UseCacheTiers;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;

final readonly class PromoteCachedValueToFasterTier
{
    public function __construct(
        private TieredCache $tieredCache,
        private Clock       $clock
    ) {}

    public function promote(CacheKey $key, ?StoredCacheRecord $record = null) : bool
    {
        if ($record === null) {
            foreach ($this->tieredCache->tiers as $tier => $store) {
                if ($store === null) {
                    continue;
                }

                $result = $store->read($key, $this->clock);

                if ($result instanceof CacheStoreRecordWasFound) {
                    $record = $result->record;
                    break;
                }
            }

            if ($record === null) {
                return false;
            }
        }

        $this->tieredCache->forget($key);
        $this->tieredCache->write($key, $record);

        return true;
    }
}