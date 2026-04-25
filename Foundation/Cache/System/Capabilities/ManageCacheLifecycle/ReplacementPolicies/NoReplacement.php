<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ReplacementPolicies;

final readonly class NoReplacement implements ChooseCachedValueForReplacement
{
    public function choose(array $entries) : ?string
    {
        return null;
    }
}