<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter;

final readonly class RateLimiter
{
    public function __construct(private RedisRateLimiter $limiter = new RedisRateLimiter) {}

    public function attempt(string $key, int $maxAttempts, int $decaySeconds = 60) : RateLimitDecision
    {
        $allowed = $this->limiter->attempt(
            key         : $key,
            maxAttempts : $maxAttempts,
            decaySeconds: $decaySeconds,
        );

        return new RateLimitDecision(
            allowed     : $allowed,
            limit       : $maxAttempts,
            remaining   : $this->limiter->remaining(key: $key, maxAttempts: $maxAttempts),
            retryAfter  : $this->limiter->availableIn(key: $key, decaySeconds: $decaySeconds),
            resetSeconds: $decaySeconds,
        );
    }
}
