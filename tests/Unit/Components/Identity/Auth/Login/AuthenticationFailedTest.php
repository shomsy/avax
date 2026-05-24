<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AuthenticationFailedTest extends TestCase
{
    #[Test]
    public function test_authentication_failed_is_runtime_exception(): void
    {
        $exception = new AuthenticationFailed('Invalid credentials');

        self::assertInstanceOf(\RuntimeException::class, $exception);
        self::assertSame('Invalid credentials', $exception->getMessage());
    }
}
