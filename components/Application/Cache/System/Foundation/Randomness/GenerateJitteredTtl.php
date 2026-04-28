<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation\Randomness;

use Avax\Components\Application\Cache\System\Foundation\Time\Duration;

final readonly class GenerateJitteredTtl
{
    public function __construct(
        private RandomJitter $jitter = new RandomJitter()
    ) {}

    public function forSeconds(int $ttlInSeconds) : int
    {
        return $this->jitter->apply(ttlInSeconds: $ttlInSeconds);
    }

    public function forDuration(Duration $duration) : Duration
    {
        $milliseconds = $this->forMilliseconds(ttlInMilliseconds: $duration->toMilliseconds());

        return Duration::ofMilliseconds(milliseconds: $milliseconds);
    }

    public function forMilliseconds(int $ttlInMilliseconds) : int
    {
        return $this->jitter->applyToDuration(ttlInMilliseconds: $ttlInMilliseconds);
    }
}