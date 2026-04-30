<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Randomness;

use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Random\RandomException;

final readonly class JitterCacheTtl
{
    public function __construct(
        private int $jitterPercent = 10,
    ) {}

    public function addJitterToTtl(int $ttlSeconds) : int
    {
        return $this->addJitter(ttlSeconds: $ttlSeconds);
    }

    /**
     * @throws RandomException
     */
    public function addJitter(int $ttlSeconds) : int
    {
        if ($ttlSeconds <= 0 || $this->jitterPercent <= 0) {
            return $ttlSeconds;
        }

        $maxJitter = (int) ($ttlSeconds * $this->jitterPercent / 100);

        if ($maxJitter < 1) {
            $maxJitter = 1;
        }

        $jitter = random_int(0, $maxJitter * 2) - $maxJitter;

        return max(1, $ttlSeconds + $jitter);
    }

    public function jitterForDuration(int $ttlSeconds) : Duration
    {
        $jittered = $this->addJitter(ttlSeconds: $ttlSeconds);

        return Duration::ofSeconds(seconds: $jittered);
    }
}
