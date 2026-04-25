<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Foundation\Time\Clock;

final class NullCacheStore implements CacheStore
{
    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        return new CacheStoreRecordWasMissing($key);
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void {}

    public function forget(CacheKey $key) : void {}

    public function clear() : void {}

    public function exists(CacheKey $key) : bool
    {
        return false;
    }
}