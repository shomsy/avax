<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Resilience;

use Avax\Components\Operations\Resilience\System\Capabilities\RateLimiter\RateLimitDecision;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RateLimitDecisionTest extends TestCase
{
    #[Test]
    public function it_stores_decision_properties() : void
    {
        $decision = new RateLimitDecision(
            allowed     : true,
            limit       : 100,
            remaining   : 95,
            retryAfter  : 0,
            resetSeconds: 60,
        );

        self::assertTrue($decision->allowed);
        self::assertSame(100, $decision->limit);
        self::assertSame(95, $decision->remaining);
        self::assertSame(0, $decision->retryAfter);
        self::assertSame(60, $decision->resetSeconds);
    }

    #[Test]
    public function it_stores_denied_decision() : void
    {
        $decision = new RateLimitDecision(
            allowed     : false,
            limit       : 10,
            remaining   : 0,
            retryAfter  : 30,
            resetSeconds: 60,
        );

        self::assertFalse($decision->allowed);
        self::assertSame(10, $decision->limit);
        self::assertSame(0, $decision->remaining);
        self::assertSame(30, $decision->retryAfter);
    }

    #[Test]
    public function it_generates_rate_limit_headers_for_allowed_request() : void
    {
        $decision = new RateLimitDecision(
            allowed     : true,
            limit       : 100,
            remaining   : 99,
            retryAfter  : 0,
            resetSeconds: 60,
        );

        $headers = $decision->headers();

        self::assertSame([
                             'X-RateLimit-Limit' => '100',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    'X-RateLimit-Remaining' => '99',
                             'X-RateLimit-Reset' => '60',
                             'Retry-After'       => '0',
                         ], $headers);
    }

    #[Test]
    public function it_generates_rate_limit_headers_for_denied_request() : void
    {
        $decision = new RateLimitDecision(
            allowed     : false,
            limit       : 10,
            remaining   : 0,
            retryAfter  : 45,
            resetSeconds: 60,
        );

        $headers = $decision->headers();

        self::assertSame([
                             'X-RateLimit-Limit' => '10',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    'X-RateLimit-Remaining' => '0',
                             'X-RateLimit-Reset' => '60',
                             'Retry-After'       => '45',
                         ], $headers);
    }

    #[Test]
    public function it_converts_all_values_to_strings_in_headers() : void
    {
        $decision = new RateLimitDecision(
            allowed     : true,
            limit       : 1,
            remaining   : 0,
            retryAfter  : 12345,
            resetSeconds: 3600,
        );

        $headers = $decision->headers();

        foreach ($headers as $value) {
            self::assertIsString($value);
        }
    }

    #[Test]
    public function it_includes_all_expected_header_keys() : void
    {
        $decision = new RateLimitDecision(
            allowed     : true,
            limit       : 50,
            remaining   : 25,
            retryAfter  : 10,
            resetSeconds: 120,
        );

        $headers = $decision->headers();

        self::assertArrayHasKey('X-RateLimit-Limit', $headers);
        self::assertArrayHasKey('X-RateLimit-Remaining', $headers);
        self::assertArrayHasKey('X-RateLimit-Reset', $headers);
        self::assertArrayHasKey('Retry-After', $headers);
    }

    #[Test]
    public function it_has_exactly_four_headers() : void
    {
        $decision = new RateLimitDecision(
            allowed     : true,
            limit       : 10,
            remaining   : 5,
            retryAfter  : 0,
            resetSeconds: 30,
        );

        self::assertCount(4, $decision->headers());
    }

    #[Test]
    public function it_is_fully_readonly() : void
    {
        $decision = new RateLimitDecision(
            allowed     : false,
            limit       : 100,
            remaining   : 0,
            retryAfter  : 60,
            resetSeconds: 120,
        );

        self::assertFalse($decision->allowed);
        self::assertSame(100, $decision->limit);
        self::assertSame(0, $decision->remaining);
        self::assertSame(60, $decision->retryAfter);
        self::assertSame(120, $decision->resetSeconds);
    }

    #[Test]
    public function it_handles_zero_values() : void
    {
        $decision = new RateLimitDecision(
            allowed     : true,
            limit       : 0,
            remaining   : 0,
            retryAfter  : 0,
            resetSeconds: 0,
        );

        self::assertTrue($decision->allowed);
        self::assertSame(0, $decision->limit);
        self::assertSame(0, $decision->remaining);
        self::assertSame(0, $decision->retryAfter);
        self::assertSame(0, $decision->resetSeconds);

        $headers = $decision->headers();
        self::assertSame('0', $headers['X-RateLimit-Limit']);
        self::assertSame('0', $headers['X-RateLimit-Remaining']);
        self::assertSame('0', $headers['X-RateLimit-Reset']);
        self::assertSame('0', $headers['Retry-After']);
    }

    #[Test]
    public function it_handles_large_values() : void
    {
        $decision = new RateLimitDecision(
            allowed     : false,
            limit       : 1000000,
            remaining   : 999999,
            retryAfter  : 86400,
            resetSeconds: 86400,
        );

        $headers = $decision->headers();

        self::assertSame('1000000', $headers['X-RateLimit-Limit']);
        self::assertSame('999999', $headers['X-RateLimit-Remaining']);
        self::assertSame('86400', $headers['X-RateLimit-Reset']);
        self::assertSame('86400', $headers['Retry-After']);
    }

    #[Test]
    public function allowed_decision_with_remaining() : void
    {
        $decision = new RateLimitDecision(
            allowed     : true,
            limit       : 100,
            remaining   : 50,
            retryAfter  : 0,
            resetSeconds: 60,
        );

        self::assertTrue($decision->allowed);
        self::assertGreaterThan(0, $decision->remaining);
    }

    #[Test]
    public function denied_decision_with_zero_remaining() : void
    {
        $decision = new RateLimitDecision(
            allowed     : false,
            limit       : 10,
            remaining   : 0,
            retryAfter  : 30,
            resetSeconds: 60,
        );

        self::assertFalse($decision->allowed);
        self::assertSame(0, $decision->remaining);
        self::assertGreaterThan(0, $decision->retryAfter);
    }
}
