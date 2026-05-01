<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

interface DecideCachedValueState
{
    public function decide(
        CachedValueLifecycle|null $lifecycle,
        Clock $clock,
        bool $wasExplicitlyInvalidated = false,
        bool $wasEvicted = false,
    ) : CachedValueState;
}
