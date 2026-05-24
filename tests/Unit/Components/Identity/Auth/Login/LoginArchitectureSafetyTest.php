<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Flows\Login\Login;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Flows\Login\VerifyPassword;
use Avax\Components\Identity\Auth\System\Flows\Login\StartAuthenticatedSession;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Login\FindUserByCredentials;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;
use Avax\Components\Identity\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Components\Identity\Auth\System\Capabilities\PasswordHashing\PasswordHasherInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Architecture safety assertions for the login runtime slice.
 *
 * These tests verify structural invariants that protect against
 * accidental architectural regression.
 */
final class LoginArchitectureSafetyTest extends TestCase
{
    #[Test]
    public function test_login_class_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(Login::class);
        self::assertTrue($reflection->isFinal(), 'Login must be final');
        self::assertTrue($reflection->isReadonly(), 'Login must be readonly');
    }

    #[Test]
    public function test_credentials_class_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(Credentials::class);
        self::assertTrue($reflection->isFinal(), 'Credentials must be final');
        self::assertTrue($reflection->isReadonly(), 'Credentials must be readonly');
    }

    #[Test]
    public function test_authentication_result_class_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(AuthenticationResult::class);
        self::assertTrue($reflection->isFinal(), 'AuthenticationResult must be final');
        self::assertTrue($reflection->isReadonly(), 'AuthenticationResult must be readonly');
    }

    #[Test]
    public function test_authentication_failed_is_runtime_exception(): void
    {
        $reflection = new ReflectionClass(AuthenticationFailed::class);
        self::assertTrue($reflection->isSubclassOf(\RuntimeException::class));
    }

    #[Test]
    public function test_auth_class_is_final(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->isFinal(), 'Auth must be final');
    }

    #[Test]
    public function test_verify_password_class_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(VerifyPassword::class);
        self::assertTrue($reflection->isFinal(), 'VerifyPassword must be final');
        self::assertTrue($reflection->isReadonly(), 'VerifyPassword must be readonly');
    }

    #[Test]
    public function test_password_hasher_interface_exists(): void
    {
        self::assertTrue(interface_exists(PasswordHasherInterface::class));
        self::assertTrue(class_exists(PasswordHasher::class));
        self::assertInstanceOf(PasswordHasherInterface::class, new PasswordHasher());
    }

    #[Test]
    public function test_login_rate_limit_class_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(LoginRateLimit::class);
        self::assertTrue($reflection->isFinal(), 'LoginRateLimit must be final');
        self::assertTrue($reflection->isReadonly(), 'LoginRateLimit must be readonly');
    }
}
