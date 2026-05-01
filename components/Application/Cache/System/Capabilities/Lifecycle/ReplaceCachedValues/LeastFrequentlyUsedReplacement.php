<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

use Override;

final readonly class LeastFrequentlyUsedReplacement implements ChooseCachedValueForReplacement
{
    #[Override]
    public function choose(array $entries) : ?string
    {
        if ($entries === []) {
            return null;
        }

        $leastUsed         = null;
        $lowestCount = PHP_INT_MAX;

        foreach ($entries as $key => $lifecycle) {
            if ($lifecycle->hitCount < $lowestCount) {
                $lowestCount = $lifecycle->hitCount;
                $leastUsed = $key;
            }
        }

        return $leastUsed;
    }
}
