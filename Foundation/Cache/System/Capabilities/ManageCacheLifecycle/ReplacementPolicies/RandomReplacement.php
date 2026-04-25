<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\ReplacementPolicies;

use Random\Randomizer;

final readonly class RandomReplacement implements ChooseCachedValueForReplacement
{
    public function __construct(
        private ?Randomizer $randomizer = null
    ) {}

    public function choose(array $entries) : ?string
    {
        if (count($entries) === 0) {
            return null;
        }

        $keys        = array_keys($entries);
        $randomIndex = random_int(min: 0, max: count($keys) - 1);

        return $keys[$randomIndex];
    }
}