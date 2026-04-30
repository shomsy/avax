<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

use Override;
use Random\RandomException;

final readonly class RandomReplacement implements ChooseCachedValueForReplacement
{
    /**
     * @throws RandomException
     */
    #[Override]
    public function choose(array $entries) : string|null
    {
        if ($entries === []) {
            return null;
        }

        $keys        = array_keys($entries);
        $randomIndex = random_int(min: 0, max: count($keys) - 1);

        return $keys[$randomIndex];
    }
}
