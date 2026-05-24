<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RateLimitExceptionTest extends TestCase
{
    #[Test]
    public function test_rate_limit_exception_contains_retry_after(): void
    {
        $exception = new RateLimitException('Too many attempts', retryAfter: 30);

        self::assertSame('Too many attempts', $exception->getMessage());
        self::assertSame(30, $exception->getRetryAfter());
    }

    #[Test]
    public function test_rate_limit_exception_has_http_status_code(): void
    {
        $exception = new RateLimitException('Too many attempts', retryAfter: 30);

        self::assertSame(429, $exception->getCode());
    }
}
