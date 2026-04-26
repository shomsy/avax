<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Login\RateLimit;

use Avax\Auth\System\Flows\Login\RateLimit\InMemoryLoginRateLimitStorage;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for login rate limiting.
 */
class LoginRateLimitTest extends TestCase
{
    /**
     * @throws RateLimitException
     */
    public function testLoginRateLimitAllowsRequestsBelowThreshold() : void
    {
        $storage   = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 2, decaySeconds: 60);

        $rateLimit->check(identifier: 'alice');

        $this->assertSame(expected: 0, actual: $storage->get(identifier: 'alice'));
    }

    /**
     * @throws RateLimitException
     */
    public function testLoginRateLimitBlocksWithinDecayWindow() : void
    {
        $storage   = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 60);

        $rateLimit->recordFailed(identifier: 'alice');

        $this->expectException(exception: RateLimitException::class);
        $rateLimit->check(identifier: 'alice');
    }

    /**
     * @throws RateLimitException
     */
    public function testLoginRateLimitResetsAfterDecayWindow() : void
    {
        $storage   = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 0);

        $rateLimit->recordFailed(identifier: 'alice');
        $rateLimit->check(identifier: 'alice');

        $this->assertSame(expected: 0, actual: $storage->get(identifier: 'alice'));
        $this->assertSame(expected: 0, actual: $storage->getLastAttemptTime(identifier: 'alice'));
    }

    /**
     * @throws RateLimitException
     */
    public function testLoginRateLimitNormalizesIdentifiers() : void
    {
        $storage   = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 60);

        $rateLimit->recordFailed(identifier: 'Alice@Example.com');

        $this->expectException(exception: RateLimitException::class);
        $rateLimit->check(identifier: 'alice@example.com');
    }
}
