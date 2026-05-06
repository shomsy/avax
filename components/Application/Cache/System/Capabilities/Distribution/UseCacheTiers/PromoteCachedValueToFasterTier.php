<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final readonly class PromoteCachedValueToFasterTier
{
    public function __construct(
        private TieredCache $tieredCache,
        private Clock $clock,
    ) {
    }

    public function promote(CacheKey $cacheKey, ?StoredCacheRecord $storedCacheRecord = null): bool
    {
        if (! $storedCacheRecord instanceof StoredCacheRecord) {
            foreach ($this->tieredCache->stores() as $store) {
                if ($store === null) {
                    continue;
                }

                $result = $store->read(cacheKey: $cacheKey, clock: $this->clock);

                if ($result instanceof CacheStoreRecordWasFound) {
                    $storedCacheRecord = $result->storedCacheRecord;

                    break;
                }
            }

            if ($storedCacheRecord === null) {
                return false;
            }
        }

        $this->tieredCache->forget(cacheKey: $cacheKey);
        $this->tieredCache->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);

        return true;
    }
}
