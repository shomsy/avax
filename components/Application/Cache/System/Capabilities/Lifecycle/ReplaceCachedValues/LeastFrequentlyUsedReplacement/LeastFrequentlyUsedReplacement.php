<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastFrequentlyUsedReplacement;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\TrackCachedValueAccess;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Override;

final readonly class LeastFrequentlyUsedReplacement implements ChooseCachedValueForReplacement, TrackCachedValueAccess
{
    private FrequencyTracker $frequencyTracker;

    public function __construct(
        Clock|null $clock = null,
        float  $decayFactor = 0.5,
    )
    {
        $this->frequencyTracker = new FrequencyTracker(clock: $clock ?? new SystemClock(), decayFactor: $decayFactor);
    }

    #[Override]
    public function choose(array $entries) : string|null
    {
        if ($entries === []) {
            return null;
        }

        return $this->frequencyTracker->getLeastFrequent(keys: array_keys($entries));
    }

    public function recordAccess(string $key) : void
    {
        $this->frequencyTracker->recordAccess(key: $key);
    }

    public function getFrequency(string $key) : int
    {
        return $this->frequencyTracker->getFrequency(key: $key);
    }

    public function removeKey(string $key) : void
    {
        $this->frequencyTracker->remove(key: $key);
    }

    public function reset() : void
    {
        $this->frequencyTracker->reset();
    }
}
