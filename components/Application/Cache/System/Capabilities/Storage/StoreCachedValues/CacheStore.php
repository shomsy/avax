<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

interface CacheStore
{
    public function read(CacheKey $cacheKey, Clock $clock): CacheStoreRecordWasFound|CacheStoreRecordWasMissing;

    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void;

    public function forget(CacheKey $cacheKey): void;

    public function clear(): void;

    public function exists(CacheKey $cacheKey): bool;
}
