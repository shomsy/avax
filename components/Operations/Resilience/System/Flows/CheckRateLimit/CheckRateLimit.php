<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\CheckRateLimit;

use Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter\RateLimiter;

final readonly class CheckRateLimit
{
    public function check(RateLimiter $limiter, string $key, int $maxAttempts = 100, int $decaySeconds = 60) : bool
    {
        $decision = $limiter->attempt(key: $key, maxAttempts: $maxAttempts, decaySeconds: $decaySeconds);

        return $decision->allowed;
    }
}
