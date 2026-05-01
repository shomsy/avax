<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\RefreshCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final readonly class ShouldRefreshCachedValue
{
    public function __construct(
        private Clock $clock,
        private RefreshPolicy $refreshPolicy = RefreshPolicy::DO_NOT_REFRESH,
        private int $refreshAheadWindowSeconds = 60,
        private int $staleThresholdSeconds = 300,
    ) {}

    public function shouldRefresh(?CachedValueLifecycle $lifecycle) : bool
    {
        return match ($this->refreshPolicy) {
            RefreshPolicy::DO_NOT_REFRESH     => false,
            RefreshPolicy::REFRESH_ON_READ    => $this->shouldRefreshOnRead(lifecycle: $lifecycle),
            RefreshPolicy::REFRESH_AHEAD      => $this->shouldRefreshAhead(lifecycle: $lifecycle),
            RefreshPolicy::REFRESH_WHEN_STALE => $this->shouldRefreshWhenStale(lifecycle: $lifecycle),
            RefreshPolicy::REFRESH_AFTER_WRITE => true,
        };
    }

    private function shouldRefreshOnRead(?CachedValueLifecycle $lifecycle) : bool
    {
        if (! $lifecycle instanceof CachedValueLifecycle) {
            return true;
        }

        return $lifecycle->isExpired(clock: $this->clock);
    }

    private function shouldRefreshAhead(?CachedValueLifecycle $lifecycle) : bool
    {
        if (! $lifecycle instanceof CachedValueLifecycle) {
            return true;
        }

        $ttl = $lifecycle->timeToLive(clock: $this->clock);

        return $ttl <= $this->refreshAheadWindowSeconds;
    }

    private function shouldRefreshWhenStale(?CachedValueLifecycle $lifecycle) : bool
    {
        if (! $lifecycle instanceof CachedValueLifecycle) {
            return true;
        }

        return $lifecycle->idleTime(clock: $this->clock) > $this->staleThresholdSeconds;
    }
}
