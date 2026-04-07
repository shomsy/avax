<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Login\RateLimit;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flow\Login\RateLimit\InMemoryLoginRateLimitStorage;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flow\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Foundation\Clock;

/**
 * Unit test for login rate limiting.
 */
class LoginRateLimitTest extends TestCase
{
    public function testLoginRateLimitAllowsRequestsBelowThreshold() : void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 2, decaySeconds: 60);

        $rateLimit->check(identifier: 'alice');

        $this->assertSame(expected: 0, actual: $storage->get(identifier: 'alice'));
    }

    public function testLoginRateLimitBlocksWithinDecayWindow() : void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 60);

        $rateLimit->recordFailed(identifier: 'alice');

        $this->expectException(exception: RateLimitException::class);
        $rateLimit->check(identifier: 'alice');
    }

    public function testLoginRateLimitResetsAfterDecayWindow() : void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 0);

        $rateLimit->recordFailed(identifier: 'alice');
        $rateLimit->check(identifier: 'alice');

        $this->assertSame(expected: 0, actual: $storage->get(identifier: 'alice'));
        $this->assertSame(expected: 0, actual: $storage->getLastAttemptTime(identifier: 'alice'));
    }

    public function testLoginRateLimitNormalizesIdentifiers() : void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 60);

        $rateLimit->recordFailed(identifier: 'Alice@Example.com');

        $this->expectException(exception: RateLimitException::class);
        $rateLimit->check(identifier: 'alice@example.com');
    }
}
