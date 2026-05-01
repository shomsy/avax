<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Override;

final class NullCacheStore implements CacheStore
{
    #[Override]
    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        return new CacheStoreRecordWasMissing(key: $key);
    }

    #[Override]
    public function write(CacheKey $key, StoredCacheRecord $record) : void {}

    #[Override]
    public function forget(CacheKey $key) : void {}

    #[Override]
    public function clear() : void {}

    #[Override]
    public function exists(CacheKey $key) : bool
    {
        return false;
    }
}
