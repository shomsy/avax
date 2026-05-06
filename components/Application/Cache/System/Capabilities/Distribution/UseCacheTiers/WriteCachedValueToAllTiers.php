<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;

final readonly class WriteCachedValueToAllTiers
{
    public function __construct(
        private TieredCache $tieredCache,
    ) {}

    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void
    {
        $this->tieredCache->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
    }
}
