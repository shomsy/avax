<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues;

use Random\RandomException;
use Random\Randomizer;

final readonly class RandomReplacement implements ChooseCachedValueForReplacement
{
    public function __construct(
        private Randomizer|null $randomizer = null
    ) {}

    /**
     * @throws RandomException
     */
    public function choose(array $entries) : string|null
    {
        if (count($entries) === 0) {
            return null;
        }

        $keys        = array_keys($entries);
        $randomIndex = random_int(min: 0, max: count($keys) - 1);

        return $keys[$randomIndex];
    }
}