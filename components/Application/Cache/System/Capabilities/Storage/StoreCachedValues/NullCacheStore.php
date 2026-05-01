<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Override;

final class NullCacheStore implements CacheStore
{
    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock) : CacheStoreRecordWasMissing
    {
        return new CacheStoreRecordWasMissing(key: $cacheKey);
    }

    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord) : void {}

    #[Override]
    public function forget(CacheKey $cacheKey) : void {}

    #[Override]
    public function clear() : void {}

    #[Override]
    public function exists(CacheKey $cacheKey) : bool
    {
        return false;
    }
}
