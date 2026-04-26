<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Cache\System\Foundation\Time\Clock;

final readonly class StoredCacheRecord
{
    public function __construct(
        public mixed                $value,
        public CachedValueLifecycle $lifecycle,
        public string|null          $serializedData = null,
        public string|null          $format = null
    ) {}

    public function isExpired(Clock $clock) : bool
    {
        return $this->lifecycle->isExpired(clock: $clock);
    }

    public function timeToLive(Clock $clock) : int
    {
        return $this->lifecycle->timeToLive(clock: $clock);
    }

    public function age(Clock $clock) : int
    {
        return $this->lifecycle->age(clock: $clock);
    }
}