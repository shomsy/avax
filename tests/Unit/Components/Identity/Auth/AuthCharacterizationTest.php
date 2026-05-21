<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth;

use Avax\Components\Identity\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationResult;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthCharacterizationTest extends TestCase
{
    #[Test]
    public function classIsFinalReadonly(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->isFinal(), 'Auth must be final');
    }

    #[Test]
    public function implementsAuthInterface(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        self::assertTrue($reflection->implementsInterface(AuthInterface::class));
    }

    #[Test]
    public function authInterfaceMethodsAreImplemented(): void
    {
        $interfaceMethods = (new ReflectionClass(AuthInterface::class))->getMethods();
        $classMethods = (new ReflectionClass(Auth::class))->getMethods();

        foreach ($interfaceMethods as $ifaceMethod) {
            $name = $ifaceMethod->getName();
            $found = false;
            foreach ($classMethods as $clsMethod) {
                if ($clsMethod->getName() === $name) {
                    $found = true;
                    self::assertSame(
                        (string) $ifaceMethod->getReturnType(),
                        (string) $clsMethod->getReturnType(),
                        "Auth::{$name}() return type must match AuthInterface",
                    );
                    break;
                }
            }
            self::assertTrue($found, "Auth must implement AuthInterface::{$name}()");
        }
    }

    #[Test]
    public function methodSignaturesAreStable(): void
    {
        $reflection = new ReflectionClass(Auth::class);

        $login = $reflection->getMethod('login');
        self::assertEquals([Credentials::class], $this->getParameterTypes($login));
        self::assertEquals(AuthenticationResult::class, (string) $login->getReturnType());

        $user = $reflection->getMethod('user');
        self::assertStringContainsString('User', (string) $user->getReturnType());

        $check = $reflection->getMethod('check');
        self::assertEquals('bool', (string) $check->getReturnType());

        $guest = $reflection->getMethod('guest');
        self::assertEquals('bool', (string) $guest->getReturnType());

        $register = $reflection->getMethod('register');
        self::assertEquals([RegistrationData::class], $this->getParameterTypes($register));
        self::assertEquals(RegistrationResult::class, (string) $register->getReturnType());

        $changePassword = $reflection->getMethod('changePassword');
        self::assertEquals([ChangePasswordData::class], $this->getParameterTypes($changePassword));
        self::assertEquals('void', (string) $changePassword->getReturnType());

        $logout = $reflection->getMethod('logout');
        self::assertEquals('void', (string) $logout->getReturnType());

        $logoutAllSessions = $reflection->getMethod('logoutAllSessions');
        self::assertEquals('void', (string) $logoutAllSessions->getReturnType());
    }

    #[Test]
    public function publicSurfaceDoesNotExposeInternalAssemblyTypes(): void
    {
        $reflection = new ReflectionClass(Auth::class);
        $internalTypes = [
            'AssembleAuthIdentityGraph',
            'AuthCapabilityReadiness',
            'AssembleAuthExternalIdentityGraph',
            'Identity',
        ];

        foreach ($reflection->getMethods() as $method) {
            $returnType = $method->getReturnType();
            if ($returnType === null) {
                continue;
            }
            $returnName = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : '';
            $shortName = explode('\\', $returnName);
            $shortName = end($shortName);
            foreach ($internalTypes as $internalType) {
                self::assertNotSame(
                    $internalType,
                    $shortName,
                    "Auth::{$method->getName()}() should not expose internal type {$internalType}",
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    private function getParameterTypes(\ReflectionMethod $method): array
    {
        $types = [];
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $types[] = $type instanceof \ReflectionNamedType ? $type->getName() : 'mixed';
        }
        return $types;
    }
}
