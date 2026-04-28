<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

final readonly class LeastFrequentlyUsedReplacement implements ChooseCachedValueForReplacement
{
    public function choose(array $entries) : string|null
    {
        if (count($entries) === 0) {
            return null;
        }

        $leastUsed   = null;
        $lowestCount = PHP_INT_MAX;

        foreach ($entries as $key => $lifecycle) {
            if ($lifecycle->hitCount < $lowestCount) {
                $lowestCount = $lifecycle->hitCount;
                $leastUsed   = $key;
            }
        }

        return $leastUsed;
    }
}