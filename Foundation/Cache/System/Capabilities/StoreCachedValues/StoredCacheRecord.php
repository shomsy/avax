<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Foundation\Time\Clock;

final readonly class StoredCacheRecord
{
    public function __construct(
        public mixed                $value,
        public CachedValueLifecycle $lifecycle,
        public ?string              $serializedData = null,
        public ?string              $format = null
    ) {}

    public function isExpired(Clock $clock) : bool
    {
        return $this->lifecycle->isExpired($clock);
    }

    public function timeToLive(Clock $clock) : int
    {
        return $this->lifecycle->timeToLive($clock);
    }

    public function age(Clock $clock) : int
    {
        return $this->lifecycle->age($clock);
    }
}