<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

final readonly class LeastRecentlyUsedReplacement implements ChooseCachedValueForReplacement
{
    public function choose(array $entries) : string|null
    {
        if (count($entries) === 0) {
            return null;
        }

        $oldest     = null;
        $oldestTime = PHP_INT_MAX;

        foreach ($entries as $key => $lifecycle) {
            $accessedAt = $lifecycle->lastAccessedAt->toUnixTime();

            if ($accessedAt < $oldestTime) {
                $oldestTime = $accessedAt;
                $oldest     = $key;
            }
        }

        return $oldest;
    }
}