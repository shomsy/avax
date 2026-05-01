<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Override;

final readonly class CacheLifecyclePolicy implements DecideCachedValueState
{
    public function __construct(
        private int $expiringSoonThresholdSeconds = 60,
        private int $staleGracePeriodSeconds = 300,
    ) {
    }

    #[Override]
    public function decide(
        ?CachedValueLifecycle $cachedValueLifecycle,
        Clock $clock,
        bool $wasExplicitlyInvalidated = false,
        bool $wasEvicted = false,
    ): CachedValueState {
        if ($wasEvicted) {
            return CachedValueState::EVICTED;
        }

        if ($wasExplicitlyInvalidated) {
            return CachedValueState::INVALIDATED;
        }

        if (! $cachedValueLifecycle instanceof CachedValueLifecycle) {
            return CachedValueState::MISSING;
        }

        if ($cachedValueLifecycle->isExpired(clock: $clock)) {
            return CachedValueState::EXPIRED;
        }

        $ttl = $cachedValueLifecycle->timeToLive(clock: $clock);

        if ($ttl <= $this->expiringSoonThresholdSeconds) {
            return CachedValueState::EXPIRING_SOON;
        }

        $idleSeconds = $cachedValueLifecycle->idleTime(clock: $clock);

        if ($idleSeconds > $this->staleGracePeriodSeconds) {
            return CachedValueState::STALE;
        }

        return CachedValueState::ACTIVE;
    }
}
