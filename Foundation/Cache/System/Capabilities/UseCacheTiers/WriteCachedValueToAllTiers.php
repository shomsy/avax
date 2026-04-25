<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\UseCacheTiers;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;

final readonly class WriteCachedValueToAllTiers
{
    public function __construct(
        private TieredCache $tieredCache,
        private Clock       $clock
    ) {}

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $this->tieredCache->write($key, $record);
    }
}