<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\PublicSurface;

use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthPublicSurfaceTest extends TestCase
{
    #[Test]
    public function test_auth_interface_is_implemented(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->implementsInterface(AuthInterface::class));
    }

    #[Test]
    public function test_auth_class_is_final(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->isFinal(), 'Auth must be final');
    }

    #[Test]
    public function test_auth_has_login_method(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->hasMethod('login'));

        $method = $reflection->getMethod('login');
        self::assertTrue($method->isPublic());
    }

    #[Test]
    public function test_auth_has_logout_method(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->hasMethod('logout'));

        $method = $reflection->getMethod('logout');
        self::assertTrue($method->isPublic());
        self::assertEquals('void', (string) $method->getReturnType());
    }

    #[Test]
    public function test_auth_has_check_method(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->hasMethod('check'));

        $method = $reflection->getMethod('check');
        self::assertTrue($method->isPublic());
        self::assertEquals('bool', (string) $method->getReturnType());
    }

    #[Test]
    public function test_auth_has_guest_method(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->hasMethod('guest'));

        $method = $reflection->getMethod('guest');
        self::assertTrue($method->isPublic());
        self::assertEquals('bool', (string) $method->getReturnType());
    }

    #[Test]
    public function test_auth_has_user_method(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->hasMethod('user'));

        $method = $reflection->getMethod('user');
        self::assertTrue($method->isPublic());
    }

    #[Test]
    public function test_auth_interface_methods_are_implemented(): void
    {
        $interfaceMethods = (new ReflectionClass(AuthInterface::class))->getMethods();
        $classMethods = (new ReflectionClass(Auth::class))->getMethods();

        foreach ($interfaceMethods as $ifaceMethod) {
            $name = $ifaceMethod->getName();
            $found = false;
            foreach ($classMethods as $clsMethod) {
                if ($clsMethod->getName() === $name) {
                    $found = true;
                    break;
                }
            }
            self::assertTrue($found, "Auth must implement AuthInterface::{$name}()");
        }
    }
}
