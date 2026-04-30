<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastFrequentlyUsedReplacement;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;

final class FrequencyTracker
{
    private const int DECAY_INTERVAL_SECONDS = 300;

    /** @var array<string, int> */
    private array $frequencies = [];

    /** @var array<string, int> */
    private array $lastDecayTimes = [];

    private int $lastGlobalDecay = 0;

    public function __construct(
        private readonly Clock $clock,
        private readonly float $decayFactor = 0.5,
    ) {}

    public function recordAccess(string $key) : void
    {
        $this->maybeDecay();

        $this->frequencies[$key]    = ($this->frequencies[$key] ?? 0) + 1;
        $this->lastDecayTimes[$key] = $this->now();
    }

    private function maybeDecay() : void
    {
        $now = $this->now();

        if ($now - $this->lastGlobalDecay >= self::DECAY_INTERVAL_SECONDS) {
            $this->applyGlobalDecay(now: $now);
            $this->lastGlobalDecay = $now;
        }
    }

    private function now() : int
    {
        return $this->clock->now()->toUnixTime();
    }

    private function applyGlobalDecay(int $now) : void
    {
        foreach ($this->frequencies as $key => $freq) {
            $lastDecay          = $this->lastDecayTimes[$key] ?? $now;
            $timeSinceLastDecay = $now - $lastDecay;
            $decayPeriods       = (int) floor($timeSinceLastDecay / self::DECAY_INTERVAL_SECONDS);

            if ($decayPeriods > 0) {
                $this->frequencies[$key] = (int) ($freq * $this->decayFactor ** $decayPeriods);
                $this->lastDecayTimes[$key] = $now;
            }
        }
    }

    public function getFrequency(string $key) : int
    {
        $this->maybeDecayKey(key: $key);

        return $this->frequencies[$key] ?? 0;
    }

    private function maybeDecayKey(string $key) : void
    {
        if (! isset($this->lastDecayTimes[$key])) {
            return;
        }

        $now = $this->now();

        if ($now - $this->lastDecayTimes[$key] >= self::DECAY_INTERVAL_SECONDS) {
            $this->frequencies[$key]    = (int) ($this->frequencies[$key] * $this->decayFactor);
            $this->lastDecayTimes[$key] = $now;
        }
    }

    public function remove(string $key) : void
    {
        unset($this->frequencies[$key], $this->lastDecayTimes[$key]);
    }

    public function getLeastFrequent(array $keys) : string|null
    {
        if ($keys === []) {
            return null;
        }

        $this->maybeDecay();

        $leastKey  = null;
        $leastFreq = PHP_INT_MAX;

        foreach ($keys as $key) {
            $freq = $this->frequencies[$key] ?? 0;

            if ($freq < $leastFreq) {
                $leastFreq = $freq;
                $leastKey  = $key;
            }
        }

        return $leastKey;
    }

    public function reset() : void
    {
        $this->frequencies     = [];
        $this->lastDecayTimes  = [];
        $this->lastGlobalDecay = $this->now();
    }
}
