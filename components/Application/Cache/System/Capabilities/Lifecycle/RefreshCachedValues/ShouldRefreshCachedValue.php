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
    ) {
    }

    public function shouldRefresh(CachedValueLifecycle|null $cachedValueLifecycle) : bool
    {
        return match ($this->refreshPolicy) {
            RefreshPolicy::DO_NOT_REFRESH => false,
            RefreshPolicy::REFRESH_ON_READ => $this->shouldRefreshOnRead(cachedValueLifecycle: $cachedValueLifecycle),
            RefreshPolicy::REFRESH_AHEAD => $this->shouldRefreshAhead(cachedValueLifecycle: $cachedValueLifecycle),
            RefreshPolicy::REFRESH_WHEN_STALE => $this->shouldRefreshWhenStale(cachedValueLifecycle: $cachedValueLifecycle),
            RefreshPolicy::REFRESH_AFTER_WRITE => true,
        };
    }

    private function shouldRefreshOnRead(CachedValueLifecycle|null $cachedValueLifecycle) : bool
    {
        if (! $cachedValueLifecycle instanceof CachedValueLifecycle) {
            return true;
        }

        return $cachedValueLifecycle->isExpired(clock: $this->clock);
    }

    private function shouldRefreshAhead(CachedValueLifecycle|null $cachedValueLifecycle) : bool
    {
        if (! $cachedValueLifecycle instanceof CachedValueLifecycle) {
            return true;
        }

        $ttl = $cachedValueLifecycle->timeToLive(clock: $this->clock);

        return $ttl <= $this->refreshAheadWindowSeconds;
    }

    private function shouldRefreshWhenStale(CachedValueLifecycle|null $cachedValueLifecycle) : bool
    {
        if (! $cachedValueLifecycle instanceof CachedValueLifecycle) {
            return true;
        }

        return $cachedValueLifecycle->idleTime(clock: $this->clock) > $this->staleThresholdSeconds;
    }
}
