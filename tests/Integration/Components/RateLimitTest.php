<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components;

use Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter\RedisRateLimiter;
use Avax\Tests\TestCase;

final class RateLimitTest extends TestCase
{
    public function test_rate_limiter_blocks_after_configured_attempts(): void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        self::assertTrue($limiter->attempt(key: 'integration-limit', maxAttempts: 1, decaySeconds: 60));
        self::assertFalse($limiter->attempt(key: 'integration-limit', maxAttempts: 1, decaySeconds: 60));
    }
}
