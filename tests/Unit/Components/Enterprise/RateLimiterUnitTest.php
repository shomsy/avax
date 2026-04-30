<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Enterprise;

use Avax\Components\Resilience\System\Capabilities\RateLimiter\RateLimit;
use Avax\Components\Resilience\System\Capabilities\RateLimiter\RateLimiter;
use Avax\Components\Resilience\System\Capabilities\RateLimiter\RateLimitMiddleware;
use Avax\Components\Resilience\System\Capabilities\RateLimiter\RedisRateLimiter;
use Avax\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;

final class RateLimiterUnitTest extends TestCase
{
    public function test_it_allows_attempts_below_limit() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        self::assertTrue(condition: $limiter->attempt(key: 'below-limit', maxAttempts: 2, decaySeconds: 60));
    }

    public function test_it_blocks_attempts_at_limit() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        $limiter->attempt(key: 'blocked-limit', maxAttempts: 1, decaySeconds: 60);

        self::assertFalse(condition: $limiter->attempt(key: 'blocked-limit', maxAttempts: 1, decaySeconds: 60));
    }

    public function test_it_reports_remaining_attempts() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        $limiter->attempt(key: 'remaining', maxAttempts: 3, decaySeconds: 60);

        self::assertSame(expected: 2, actual: $limiter->remaining(key: 'remaining', maxAttempts: 3));
    }

    public function test_it_clears_attempts() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        $limiter->attempt(key: 'clearable', maxAttempts: 1, decaySeconds: 60);
        $limiter->clear(key: 'clearable');

        self::assertSame(expected: 1, actual: $limiter->remaining(key: 'clearable', maxAttempts: 1));
    }

    public function test_it_reports_retry_after_seconds_when_limited() : void
    {
        $limiter = new RedisRateLimiter(config: ['driver' => 'array']);

        $limiter->attempt(key: 'retry-after', maxAttempts: 1, decaySeconds: 60);

        self::assertGreaterThanOrEqual(expected: 1, actual: $limiter->availableIn(key: 'retry-after', decaySeconds: 60));
    }

    public function test_facade_uses_configured_limiter() : void
    {
        RateLimit::useLimiter(limiter: new RedisRateLimiter(config: ['driver' => 'array']));
        RateLimit::clear(key: 'facade');

        self::assertTrue(condition: RateLimit::attempt(key: 'facade', maxAttempts: 1));
        self::assertTrue(condition: RateLimit::tooManyAttempts(key: 'facade', maxAttempts: 1));
    }

    public function test_decision_exposes_headers() : void
    {
        $decision = (new RateLimiter(limiter: new RedisRateLimiter(config: ['driver' => 'array'])))->attempt(
            key        : 'headers',
            maxAttempts: 5,
        );

        self::assertSame(expected: '5', actual: $decision->headers()['X-RateLimit-Limit']);
    }

    public function test_middleware_adds_rate_limit_headers_to_success_response() : void
    {
        $middleware = new RateLimitMiddleware(
            limiter: new RedisRateLimiter(config: ['driver' => 'array']),
            config : ['max_attempts' => 2],
        );

        $response = $middleware->handle(
            request: new ServerRequest(method: 'GET', uri: '/unit', serverParams: ['REMOTE_ADDR' => '127.0.0.1']),
            next   : static fn () : Response => new Response(status: 200),
        );

        self::assertSame(expected: '2', actual: $response->getHeaderLine(header: 'X-RateLimit-Limit'));
    }

    public function test_middleware_returns_429_when_limit_is_exhausted() : void
    {
        $limiter    = new RedisRateLimiter(config: ['driver' => 'array']);
        $middleware = new RateLimitMiddleware(
            limiter: $limiter,
            config : ['max_attempts' => 1],
        );
        $request    = new ServerRequest(method: 'GET', uri: '/blocked', serverParams: ['REMOTE_ADDR' => '127.0.0.1']);

        $middleware->handle(request: $request, next: static fn () : Response => new Response(status: 200));
        $response = $middleware->handle(request: $request, next: static fn () : Response => new Response(status: 200));

        self::assertSame(expected: 429, actual: $response->getStatusCode());
    }

    public function test_middleware_can_key_by_user_attribute() : void
    {
        $middleware = new RateLimitMiddleware(
            limiter: new RedisRateLimiter(config: ['driver' => 'array']),
            config : ['max_attempts' => 1, 'key_by' => ['user']],
        );

        $first  = (new ServerRequest(method: 'GET', uri: '/user'))->withAttribute(attribute: 'user_id', value: 1);
        $second = (new ServerRequest(method: 'GET', uri: '/user'))->withAttribute(attribute: 'user_id', value: 2);

        $middleware->handle(request: $first, next: static fn () : Response => new Response(status: 200));
        $response = $middleware->handle(request: $second, next: static fn () : Response => new Response(status: 200));

        self::assertSame(expected: 200, actual: $response->getStatusCode());
    }
}
