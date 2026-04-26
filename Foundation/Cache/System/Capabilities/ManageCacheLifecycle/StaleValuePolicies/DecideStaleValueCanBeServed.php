<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\StaleValuePolicies;

final readonly class DecideStaleValueCanBeServed
{
    public function __construct(
        private StaleValuePolicy $policy = StaleValuePolicy::DO_NOT_SERVE_STALE
    ) {}

    public function canServeStale() : bool
    {
        return $this->policy === StaleValuePolicy::SERVE_STALE_WHILE_REVALIDATING
            || $this->policy === StaleValuePolicy::SERVE_STALE_FOREVER;
    }
}