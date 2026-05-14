<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastFrequentlyUsedReplacement;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\TrackCachedValueAccess;
use Override;

final readonly class LeastFrequentlyUsedReplacement implements ChooseCachedValueForReplacement, TrackCachedValueAccess
{
    public function __construct(
        private FrequencyTracker $frequencyTracker,
    ) {
    }

    #[Override]
    public function choose(array $entries) : string|null
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
