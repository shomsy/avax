<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Capabilities\PasswordHashing;

use Avax\Components\Identity\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Components\Identity\Auth\System\Capabilities\PasswordHashing\PasswordHasherInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PasswordHasherTest extends TestCase
{
    #[Test]
    public function test_password_hasher_verifies_correct_password(): void
    {
        $hasher = new PasswordHasher();
        $hash = $hasher->hash('SecurePass123');

        self::assertTrue($hasher->verify('SecurePass123', $hash));
    }

    #[Test]
    public function test_password_hasher_rejects_wrong_password(): void
    {
        $hasher = new PasswordHasher();
        $hash = $hasher->hash('SecurePass123');

        self::assertFalse($hasher->verify('WrongPassword', $hash));
    }

    #[Test]
    public function test_password_hasher_needs_rehash_returns_boolean(): void
    {
        $hasher = new PasswordHasher();
        $hash = $hasher->hash('SecurePass123');

        $result = $hasher->needsRehash($hash);

        self::assertIsBool($result);
    }

    #[Test]
    public function test_password_hasher_implements_interface(): void
    {
        $hasher = new PasswordHasher();
        self::assertInstanceOf(PasswordHasherInterface::class, $hasher);
    }

    #[Test]
    public function test_password_hasher_produces_valid_hash_format(): void
    {
        $hasher = new PasswordHasher();
        $hash = $hasher->hash('SecurePass123');

        // PASSWORD_DEFAULT produces bcrypt which starts with $2y$ or $2b$
        self::assertMatchesRegularExpression('/^\$2[by]\$/', $hash);
    }
}
