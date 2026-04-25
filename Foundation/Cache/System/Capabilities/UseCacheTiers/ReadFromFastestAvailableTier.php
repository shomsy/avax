<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\UseCacheTiers;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Foundation\Time\Clock;

final readonly class ReadFromFastestAvailableTier
{
    public function __construct(
        private TieredCache $tieredCache
    ) {}

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        return $this->tieredCache->read($key, $clock);
    }
}