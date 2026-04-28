<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

final readonly class FirstInFirstOutReplacement implements ChooseCachedValueForReplacement
{
    public function choose(array $entries) : string|null
    {
        if (count($entries) === 0) {
            return null;
        }

        $oldest     = null;
        $oldestTime = PHP_INT_MAX;

        foreach ($entries as $key => $lifecycle) {
            $createdAt = $lifecycle->createdAt->toUnixTime();

            if ($createdAt < $oldestTime) {
                $oldestTime = $createdAt;
                $oldest     = $key;
            }
        }

        return $oldest;
    }
}