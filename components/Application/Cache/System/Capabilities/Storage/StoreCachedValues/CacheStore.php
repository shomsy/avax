<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

interface CacheStore
{
    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing;

    public function write(CacheKey $key, StoredCacheRecord $record) : void;

    public function forget(CacheKey $key) : void;

    public function clear() : void;

    public function exists(CacheKey $key) : bool;
}