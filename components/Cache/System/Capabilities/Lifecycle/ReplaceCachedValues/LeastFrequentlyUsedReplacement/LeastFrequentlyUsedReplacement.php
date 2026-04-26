<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastFrequentlyUsedReplacement;

use components\Cache\System\Capabilities\CachedValueLifecycle;
use components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use components\Cache\System\Foundation\Time\Clock;
use components\Cache\System\Foundation\Time\SystemClock;

final class LeastFrequentlyUsedReplacement implements ChooseCachedValueForReplacement
{
    private FrequencyTracker $tracker;

    public function __construct(
        Clock|null $clock = null,
        float      $decayFactor = 0.5
    )
    {
        $this->tracker = new FrequencyTracker(clock: $clock ?? new SystemClock(), decayFactor: $decayFactor);
    }

    public function choose(array $entries) : string|null
    {
        if (count($entries) === 0) {
            return null;
        }

        return $this->tracker->getLeastFrequent(keys: array_keys($entries));
    }

    public function recordAccess(string $key) : void
    {
        $this->tracker->recordAccess(key: $key);
    }

    public function getFrequency(string $key) : int
    {
        return $this->tracker->getFrequency(key: $key);
    }

    public function removeKey(string $key) : void
    {
        $this->tracker->remove(key: $key);
    }

    public function reset() : void
    {
        $this->tracker->reset();
    }
}