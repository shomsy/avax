<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle;

use Avax\Cache\System\Foundation\Time\Clock;

interface DecideCachedValueState
{
    public function decide(
        ?CachedValueLifecycle $lifecycle,
        Clock                 $clock,
        bool                  $wasExplicitlyInvalidated = false,
        bool                  $wasEvicted = false
    ) : CachedValueState;
}