<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastFrequentlyUsedReplacement;

use Avax\Components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use Avax\Components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\TrackCachedValueAccess;
use Avax\Components\Application\DateTime\System\PublicSurface\Clock;
use Avax\Components\Application\DateTime\System\PublicSurface\SystemClock;
use Override;

final readonly class LeastFrequentlyUsedReplacement implements ChooseCachedValueForReplacement, TrackCachedValueAccess
{
    private FrequencyTracker $frequencyTracker;

    public function __construct(
        ?Clock $clock = null,
        float $decayFactor = 0.5,
    ) {
        $this->frequencyTracker = new FrequencyTracker(clock: $clock ?? new SystemClock(), decayFactor: $decayFactor);
    }

    #[Override]
    public function choose(array $entries): ?string
    {
        if ($entries === []) {
            return null;
        }

        return $this->frequencyTracker->getLeastFrequent(keys: array_keys($entries));
    }

    #[Override]
    public function recordAccess(string $key): void
    {
        $this->frequencyTracker->recordAccess(key: $key);
    }

    public function getFrequency(string $key): int
    {
        return $this->frequencyTracker->getFrequency(key: $key);
    }

    #[Override]
    public function removeKey(string $key): void
    {
        $this->frequencyTracker->remove(key: $key);
    }

    #[Override]
    public function reset(): void
    {
        $this->frequencyTracker->reset();
    }
}
