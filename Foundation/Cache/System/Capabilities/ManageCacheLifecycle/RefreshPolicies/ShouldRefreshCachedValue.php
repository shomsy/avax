<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\RefreshPolicies;

use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Foundation\Time\Clock;

final readonly class ShouldRefreshCachedValue
{
    public function __construct(
        private Clock         $clock,
        private RefreshPolicy $policy = RefreshPolicy::DO_NOT_REFRESH,
        private int           $refreshAheadWindowSeconds = 60,
        private int           $staleThresholdSeconds = 300
    ) {}

    public function shouldRefresh(?CachedValueLifecycle $lifecycle) : bool
    {
        return match ($this->policy) {
            RefreshPolicy::DO_NOT_REFRESH      => false,
            RefreshPolicy::REFRESH_ON_READ     => $this->shouldRefreshOnRead($lifecycle),
            RefreshPolicy::REFRESH_AHEAD       => $this->shouldRefreshAhead($lifecycle),
            RefreshPolicy::REFRESH_WHEN_STALE  => $this->shouldRefreshWhenStale($lifecycle),
            RefreshPolicy::REFRESH_AFTER_WRITE => true,
        };
    }

    private function shouldRefreshOnRead(?CachedValueLifecycle $lifecycle) : bool
    {
        if ($lifecycle === null) {
            return true;
        }

        return $lifecycle->isExpired($this->clock);
    }

    private function shouldRefreshAhead(?CachedValueLifecycle $lifecycle) : bool
    {
        if ($lifecycle === null) {
            return true;
        }

        $ttl = $lifecycle->timeToLive($this->clock);

        return $ttl <= $this->refreshAheadWindowSeconds;
    }

    private function shouldRefreshWhenStale(?CachedValueLifecycle $lifecycle) : bool
    {
        if ($lifecycle === null) {
            return true;
        }

        return $lifecycle->idleTime($this->clock) > $this->staleThresholdSeconds;
    }
}