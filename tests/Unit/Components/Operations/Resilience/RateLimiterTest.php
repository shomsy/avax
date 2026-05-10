<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter\RateLimit;
use Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter\RateLimitDecision;
use Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter\RedisRateLimiter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    private RedisRateLimiter $rateLimiter;

    #[Test]
    public function it_allows_request_within_limit() : void
    {
        $result = RateLimit::attempt('test-user', maxAttempts: 5, decaySeconds: 60);

        self::assertTrue($result);
    }

    #[Test]
    public function it_allows_multiple_requests_up_to_limit() : void
    {
        for ($i = 0; $i < 5; $i++) {
            $result = RateLimit::attempt('test-user', maxAttempts: 5, decaySeconds: 60);
            self::assertTrue($result, "Request " . ($i + 1) . ' should be allowed');
        }
    }

    #[Test]
    public function it_blocks_request_after_limit_reached() : void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimit::attempt('test-user', maxAttempts: 5, decaySeconds: 60);
        }

        $result = RateLimit::attempt('test-user', maxAttempts: 5, decaySeconds: 60);

        self::assertFalse($result);
    }

    #[Test]
    public function it_tracks_remaining_attempts() : void
    {
        RateLimit::attempt('remaining-test', maxAttempts: 10, decaySeconds: 60);
        RateLimit::attempt('remaining-test', maxAttempts: 10, decaySeconds: 60);

        $remaining = RateLimit::remaining('remaining-test', maxAttempts: 10, decaySeconds: 60);

        self::assertSame(8, $remaining);
    }

    #[Test]
    public function it_reports_zero_remaining_at_limit() : void
    {
        for ($i = 0; $i < 3; $i++) {
            RateLimit::attempt('remaining-test', maxAttempts: 3, decaySeconds: 60);
        }

        $remaining = RateLimit::remaining('remaining-test', maxAttempts: 3, decaySeconds: 60);

        self::assertSame(0, $remaining);
    }

    #[Test]
    public function it_clears_rate_limit() : void
    {
        for ($i = 0; $i < 5; $i++) {
            RateLimit::attempt('test-user', maxAttempts: 5, decaySeconds: 60);
        }

        self::assertFalse(RateLimit::attempt('test-user', maxAttempts: 5, decaySeconds: 60));

        RateLimit::clear('test-user');

        self::assertTrue(RateLimit::attempt('test-user', maxAttempts: 5, decaySeconds: 60));
    }

    #[Test]
    public function it_detects_too_many_attempts() : void
    {
        self::assertFalse(RateLimit::tooManyAttempts('test-user', maxAttempts: 3, decaySeconds: 60));

        for ($i = 0; $i < 3; $i++) {
            RateLimit::attempt('test-user', maxAttempts: 3, decaySeconds: 60);
        }

        self::assertTrue(RateLimit::tooManyAttempts('test-user', maxAttempts: 3, decaySeconds: 60));
    }

    #[Test]
    public function it_uses_default_decay_of_60_seconds() : void
    {
        $result = RateLimit::attempt('test-user', maxAttempts: 100);

        self::assertTrue($result);
    }

    #[Test]
    public function it_tracks_independent_keys() : void
    {
        for ($i = 0; $i < 2; $i++) {
            RateLimit::attempt('api-user', maxAttempts: 2, decaySeconds: 60);
        }

        self::assertFalse(RateLimit::attempt('api-user', maxAttempts: 2, decaySeconds: 60));
        self::assertTrue(RateLimit::attempt('test-user', maxAttempts: 2, decaySeconds: 60));
    }

    #[Test]
    public function it_allows_request_after_clearing_limit() : void
    {
        RateLimit::attempt('expiry-test', maxAttempts: 1, decaySeconds: 60);

        self::assertFalse(RateLimit::attempt('expiry-test', maxAttempts: 1, decaySeconds: 60));

        RateLimit::clear('expiry-test');

        self::assertTrue(RateLimit::attempt('expiry-test', maxAttempts: 1, decaySeconds: 60));
    }

    #[Test]
    public function it_reports_available_in_time() : void
    {
        RateLimit::attempt('available-test', maxAttempts: 1, decaySeconds: 5);

        $availableIn = RateLimit::availableIn('available-test', decaySeconds: 5);

        self::assertGreaterThanOrEqual(0, $availableIn);
        self::assertLessThanOrEqual(5, $availableIn);
    }

    #[Test]
    public function it_reports_zero_available_in_when_not_limited() : void
    {
        $availableIn = RateLimit::availableIn('fresh-key', decaySeconds: 60);

        self::assertSame(0, $availableIn);
    }

    #[Test]
    public function it_handles_limit_of_one() : void
    {
        self::assertTrue(RateLimit::attempt('single-test', maxAttempts: 1, decaySeconds: 60));
        self::assertFalse(RateLimit::attempt('single-test', maxAttempts: 1, decaySeconds: 60));
    }

    #[Test]
    public function it_handles_large_limit() : void
    {
        for ($i = 0; $i < 100; $i++) {
            $result = RateLimit::attempt('bulk-test', maxAttempts: 100, decaySeconds: 60);
            self::assertTrue($result);
        }

        self::assertFalse(RateLimit::attempt('bulk-test', maxAttempts: 100, decaySeconds: 60));
    }

    #[Test]
    public function it_returns_remaining_as_non_negative() : void
    {
        for ($i = 0; $i < 10; $i++) {
            RateLimit::attempt('remaining-test', maxAttempts: 5, decaySeconds: 60);
        }

        $remaining = RateLimit::remaining('remaining-test', maxAttempts: 5, decaySeconds: 60);

        self::assertGreaterThanOrEqual(0, $remaining);
    }

    #[Test]
    public function redis_rate_limiter_attempt_returns_false_when_limited() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        $limiter->attempt('direct-test', maxAttempts: 2, decaySeconds: 60);
        $limiter->attempt('direct-test', maxAttempts: 2, decaySeconds: 60);

        self::assertFalse($limiter->attempt('direct-test', maxAttempts: 2, decaySeconds: 60));
    }

    #[Test]
    public function redis_rate_limiter_clears_key() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        $limiter->attempt('clear-direct', maxAttempts: 1, decaySeconds: 60);
        self::assertFalse($limiter->attempt('clear-direct', maxAttempts: 1, decaySeconds: 60));

        $limiter->clear('clear-direct');

        self::assertTrue($limiter->attempt('clear-direct', maxAttempts: 1, decaySeconds: 60));
    }

    #[Test]
    public function redis_rate_limiter_reports_remaining() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        $limiter->attempt('remaining-direct', maxAttempts: 5, decaySeconds: 60);

        $remaining = $limiter->remaining('remaining-direct', maxAttempts: 5, decaySeconds: 60);

        self::assertSame(4, $remaining);
    }

    #[Test]
    public function redis_rate_limiter_reports_available_in() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        $limiter->attempt('available-direct', maxAttempts: 1, decaySeconds: 10);

        $availableIn = $limiter->availableIn('available-direct', decaySeconds: 10);

        self::assertGreaterThanOrEqual(0, $availableIn);
        self::assertLessThanOrEqual(10, $availableIn);
    }

    #[Test]
    public function redis_rate_limiter_too_many_attempts() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        self::assertFalse($limiter->tooManyAttempts('too-many-direct', maxAttempts: 2, decaySeconds: 60));

        $limiter->attempt('too-many-direct', maxAttempts: 2, decaySeconds: 60);
        $limiter->attempt('too-many-direct', maxAttempts: 2, decaySeconds: 60);

        self::assertTrue($limiter->tooManyAttempts('too-many-direct', maxAttempts: 2, decaySeconds: 60));
    }

    protected function setUp() : void
    {
        $this->rateLimiter = new RedisRateLimiter(config: ['driver' => 'array']);
        RateLimit::useLimiter($this->rateLimiter);
    }

    protected function tearDown() : void
    {
        $this->rateLimiter->clear('test-user');
        $this->rateLimiter->clear('api-user');
        $this->rateLimiter->clear('bulk-test');
        $this->rateLimiter->clear('expiry-test');
        $this->rateLimiter->clear('decision-test');
        $this->rateLimiter->clear('remaining-test');
        $this->rateLimiter->clear('available-test');
    }
}
