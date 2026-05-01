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
        public CacheKey          $key,
        public StoredCacheRecord $record,
        public Clock             $clock,
    )
    {
        $this->cacheKey          = $key;
        $this->storedCacheRecord = $record;
    }

    public function value() : mixed
    {
        return $this->record->value;
    }

    public function isExpired() : bool
    {
        return $this->record->isExpired(clock: $this->clock);
    }

    public function timeToLive() : int
    {
        return $this->record->timeToLive(clock: $this->clock);
    }

    public function lifecycle() : CachedValueLifecycle
    {
        return $this->record->lifecycle;
    }
}
