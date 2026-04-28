<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

use Avax\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;

interface ChooseCachedValueForReplacement
{
    /**
     * @param array<string, CachedValueLifecycle> $entries
     */
    public function choose(array $entries) : string|null;
}