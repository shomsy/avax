<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ReplacementPolicies;

use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;

interface ChooseCachedValueForReplacement
{
    /**
     * @param array<string, CachedValueLifecycle> $entries
     */
    public function choose(array $entries) : ?string;
}