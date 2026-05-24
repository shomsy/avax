<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CredentialsTest extends TestCase
{
    #[Test]
    public function test_credentials_holds_identifier_and_password(): void
    {
        $credentials = new Credentials('milos@example.com', 'SecurePass123');

        self::assertSame('milos@example.com', $credentials->identifier);
        self::assertSame('SecurePass123', $credentials->password);
    }

    #[Test]
    public function test_credentials_supports_optional_ip_and_user_agent(): void
    {
        $credentials = new Credentials(
            identifier: 'milos@example.com',
            password: 'SecurePass123',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0',
        );

        self::assertSame('192.168.1.1', $credentials->ipAddress);
        self::assertSame('Mozilla/5.0', $credentials->userAgent);
    }

    #[Test]
    public function test_credentials_class_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(Credentials::class);
        self::assertTrue($reflection->isFinal(), 'Credentials must be final');
        self::assertTrue($reflection->isReadonly(), 'Credentials must be readonly');
    }
}
