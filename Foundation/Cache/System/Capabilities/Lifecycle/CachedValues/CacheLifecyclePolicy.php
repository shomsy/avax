<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\CachedValues;

use Avax\Cache\System\Foundation\Time\Clock;

final readonly class CacheLifecyclePolicy implements DecideCachedValueState
{
    public function __construct(
        private int $expiringSoonThresholdSeconds = 60,
        private int $staleGracePeriodSeconds = 300
    ) {}

    public function decide(
        CachedValueLifecycle|null $lifecycle,
        Clock                     $clock,
        bool                      $wasExplicitlyInvalidated = false,
        bool                      $wasEvicted = false
    ) : CachedValueState
    {
        if ($wasEvicted) {
            return CachedValueState::EVICTED;
        }

        if ($wasExplicitlyInvalidated) {
            return CachedValueState::INVALIDATED;
        }

        if ($lifecycle === null) {
            return CachedValueState::MISSING;
        }

        if ($lifecycle->isExpired(clock: $clock)) {
            return CachedValueState::EXPIRED;
        }

        $ttl = $lifecycle->timeToLive(clock: $clock);

        if ($ttl <= $this->expiringSoonThresholdSeconds) {
            return CachedValueState::EXPIRING_SOON;
        }

        $idleSeconds = $lifecycle->idleTime(clock: $clock);

        if ($idleSeconds > $this->staleGracePeriodSeconds) {
            return CachedValueState::STALE;
        }

        return CachedValueState::ACTIVE;
    }
}