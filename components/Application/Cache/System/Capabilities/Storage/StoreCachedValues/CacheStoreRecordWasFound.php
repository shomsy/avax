<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final readonly class CacheStoreRecordWasFound
{
    public CacheKey $cacheKey;

    public StoredCacheRecord $storedCacheRecord;

    public function __construct(
        public CacheKey          $cacheKey,
        public StoredCacheRecord $storedCacheRecord,
        public Clock             $clock,
    )
    {
        $this->cacheKey          = $cacheKey;
        $this->storedCacheRecord = $storedCacheRecord;
    }

    public function value() : mixed
    {
        return $this->storedCacheRecord->value;
    }

    public function isExpired() : bool
    {
        return $this->storedCacheRecord->isExpired(clock: $this->clock);
    }

    public function timeToLive() : int
    {
        return $this->storedCacheRecord->timeToLive(clock: $this->clock);
    }

    public function lifecycle() : CachedValueLifecycle
    {
        return $this->storedCacheRecord->lifecycle;
    }
}
