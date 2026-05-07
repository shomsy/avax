<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Configuration;

final readonly class ResilienceConfiguration
{
    public function __construct(
        public int  $defaultRetryAttempts = 3,
        public int  $defaultRetryBackoffMs = 200,
        public int  $defaultCircuitBreakerThreshold = 3,
        public int  $defaultCircuitBreakerCooldown = 30,
        public int  $defaultTimeoutMs = 5000,
        public int  $defaultRateLimitPerMinute = 60,
        public bool $enableIdempotency = false,
    ) {}

    public function withRetryAttempts(int $attempts) : self
    {
        return new self(
            defaultRetryAttempts          : $attempts,
            defaultRetryBackoffMs         : $this->defaultRetryBackoffMs,
            defaultCircuitBreakerThreshold: $this->defaultCircuitBreakerThreshold,
            defaultCircuitBreakerCooldown : $this->defaultCircuitBreakerCooldown,
            defaultTimeoutMs              : $this->defaultTimeoutMs,
            defaultRateLimitPerMinute     : $this->defaultRateLimitPerMinute,
            enableIdempotency             : $this->enableIdempotency,
        );
    }
}
