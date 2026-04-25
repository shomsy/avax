<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Foundation\Time\Clock;

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
        return $this->record->isExpired($this->clock);
    }

    public function timeToLive() : int
    {
        return $this->record->timeToLive($this->clock);
    }

    public function lifecycle() : CachedValueLifecycle
    {
        return $this->record->lifecycle;
    }
}