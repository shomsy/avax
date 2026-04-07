<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Login\RateLimit;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flows\Login\RateLimit\InMemoryLoginRateLimitStorage;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
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

        $rateLimit->check('alice');

        $this->assertSame(0, $storage->get('alice'));
    }

    public function testLoginRateLimitBlocksWithinDecayWindow() : void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 60);

        $rateLimit->recordFailed('alice');

        $this->expectException(RateLimitException::class);
        $rateLimit->check('alice');
    }

    public function testLoginRateLimitResetsAfterDecayWindow() : void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 0);

        $rateLimit->recordFailed('alice');
        $rateLimit->check('alice');

        $this->assertSame(0, $storage->get('alice'));
        $this->assertSame(0, $storage->getLastAttemptTime('alice'));
    }

    public function testLoginRateLimitNormalizesIdentifiers() : void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(storage: $storage, clock: new Clock(), maxAttempts: 1, decaySeconds: 60);

        $rateLimit->recordFailed('Alice@Example.com');

        $this->expectException(RateLimitException::class);
        $rateLimit->check('alice@example.com');
    }
}
