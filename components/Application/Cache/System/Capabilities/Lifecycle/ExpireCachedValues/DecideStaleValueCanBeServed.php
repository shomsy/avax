<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ExpireCachedValues;

final readonly class DecideStaleValueCanBeServed
{
    public function __construct(private StaleValuePolicy $staleValuePolicy = StaleValuePolicy::DO_NOT_SERVE_STALE) {}

    public function canServeStale() : bool
    {
        return $this->staleValuePolicy === StaleValuePolicy::SERVE_STALE_WHILE_REVALIDATING
            || $this->staleValuePolicy === StaleValuePolicy::SERVE_STALE_FOREVER;
    }
}
