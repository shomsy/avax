<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

use Override;

final readonly class LeastRecentlyUsedReplacement implements ChooseCachedValueForReplacement
{
    #[Override]
    public function choose(array $entries): ?string
    {
        if ($entries === []) {
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
