<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

final readonly class StoredCacheRecord
{
    public function __construct(
        public mixed                $value,
        public CachedValueLifecycle $lifecycle,
        public string|null          $serializedData = null,
        public string|null          $format = null
    ) {}

    /**
     * Create a new stored cache record with a lifecycle.
     *
     * @param mixed    $value The cached value
     * @param int|null $ttl   Time-to-live in seconds (null for no expiration)
     */
    public static function create(mixed $value, int|null $ttl = null, Clock|null $clock = null) : self
    {
        $clock     = $clock ?? new SystemClock();
        $now       = $clock->now();
        $expiresAt = $ttl !== null ? $now->add(Duration::ofSeconds($ttl)) : Timestamp::fromUnixTime(PHP_INT_MAX);

        return new self(
            value    : $value,
            lifecycle: CachedValueLifecycle::create($now, $expiresAt, $clock)
        );
    }

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