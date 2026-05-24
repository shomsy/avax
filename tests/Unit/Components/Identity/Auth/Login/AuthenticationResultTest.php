<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthenticationResultTest extends TestCase
{
    #[Test]
    public function test_authentication_result_success_factory_method(): void
    {
        $result = AuthenticationResult::success(
            authenticationContext: \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext::guest(reason: 'test'),
            accessToken: 'access-token-123',
            refreshToken: 'refresh-token-456',
        );

        self::assertSame('access-token-123', $result->accessToken());
        self::assertSame('refresh-token-456', $result->refreshToken());
        self::assertFalse($result->requiresMfa());
        self::assertNull($result->mfaChallenge());
    }

    #[Test]
    public function test_authentication_result_class_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(AuthenticationResult::class);
        self::assertTrue($reflection->isFinal(), 'AuthenticationResult must be final');
        self::assertTrue($reflection->isReadonly(), 'AuthenticationResult must be readonly');
    }

    #[Test]
    public function test_authentication_result_has_correct_state_after_success(): void
    {
        $result = AuthenticationResult::success(
            authenticationContext: \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext::guest(reason: 'test'),
        );

        self::assertSame(AuthenticationState::AUTHENTICATED, $result->state());
    }

    #[Test]
    public function test_authentication_result_returns_null_tokens_when_not_provided(): void
    {
        $result = AuthenticationResult::success(
            authenticationContext: \Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext::guest(reason: 'test'),
        );

        self::assertNull($result->accessToken());
        self::assertNull($result->refreshToken());
    }
}
