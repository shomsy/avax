<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

use Override;

final readonly class FirstInFirstOutReplacement implements ChooseCachedValueForReplacement
{
    #[Override]
    public function choose(array $entries) : string|null
    {
        if ($entries === []) {
            return null;
        }

        $oldest = null;
        $oldestTime = PHP_INT_MAX;

        foreach ($entries as $key => $lifecycle) {
            $createdAt = $lifecycle->createdAt->toUnixTime();

            if ($createdAt < $oldestTime) {
                $oldestTime = $createdAt;
                $oldest = $key;
            }
        }

        return $oldest;
    }
}
