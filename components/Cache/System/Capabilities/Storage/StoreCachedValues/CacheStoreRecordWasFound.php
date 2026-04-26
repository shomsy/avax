<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Storage\StoreCachedValues;

use components\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use components\Cache\System\Foundation\Time\Clock;

final readonly class CacheStoreRecordWasFound
{
    public function __construct(
        public CacheKey          $key,
        public StoredCacheRecord $record,
        public Clock             $clock
    ) {}

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