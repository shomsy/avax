<?php

declare(strict_types=1);

namespace Avax\Cache\System\Foundation\Randomness;

final readonly class RandomJitter
{
    public const DEFAULT_JITTER_PERCENTAGE = 0.1;

    public function __construct(
        private float $jitterPercentage = self::DEFAULT_JITTER_PERCENTAGE
    ) {}

    public function apply(int $ttlInSeconds) : int
    {
        if ($ttlInSeconds <= 0) {
            return $ttlInSeconds;
        }

        $jitterRange = (int) ($ttlInSeconds * $this->jitterPercentage);
        $jitter      = random_int(min: -$jitterRange, max: $jitterRange);

        return max(1, $ttlInSeconds + $jitter);
    }

    public function applyToDuration(int $ttlInMilliseconds) : int
    {
        if ($ttlInMilliseconds <= 0) {
            return $ttlInMilliseconds;
        }

        $jitterRange = (int) ($ttlInMilliseconds * $this->jitterPercentage);
        $jitter      = random_int(min: -$jitterRange, max: $jitterRange);

        return max(1, $ttlInMilliseconds + $jitter);
    }
}