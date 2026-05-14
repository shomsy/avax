<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter;

final readonly class RateLimiter
{
    public function __construct(private RedisRateLimiter $redisRateLimiter)
    {
    }

    public function attempt(string $key, int $maxAttempts, int $decaySeconds = 60): RateLimitDecision
    {
        $allowed = $this->redisRateLimiter->attempt(
            key         : $key,
            maxAttempts : $maxAttempts,
            decaySeconds: $decaySeconds,
        );

        return new RateLimitDecision(
            allowed     : $allowed,
            limit       : $maxAttempts,
            remaining   : $this->redisRateLimiter->remaining(key: $key, maxAttempts: $maxAttempts),
            retryAfter  : $this->redisRateLimiter->availableIn(key: $key, decaySeconds: $decaySeconds),
            resetSeconds: $decaySeconds,
        );
    }
}
