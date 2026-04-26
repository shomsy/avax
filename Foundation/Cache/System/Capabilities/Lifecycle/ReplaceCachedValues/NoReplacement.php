<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

final readonly class NoReplacement implements ChooseCachedValueForReplacement
{
    public function choose(array $entries) : string|null
    {
        return null;
    }
}