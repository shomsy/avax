<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Hashing\Capabilities\PasswordHashing;

use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SecurityPasswordHasherTest extends TestCase
{
    #[Test]
    public function test_password_hasher_verifies_hashes(): void
    {
        $hasher = new PasswordHasher();
        $hash = $hasher->hash('secret');

        $this->assertTrue($hasher->verify('secret', $hash));
        $this->assertFalse($hasher->verify('wrong', $hash));
    }

    #[Test]
    public function test_password_hasher_supports_argon2id_when_available(): void
    {
        if (!defined('PASSWORD_ARGON2ID')) {
            self::markTestSkipped('Argon2id not available');
        }

        $hasher = new PasswordHasher(algo: PASSWORD_ARGON2ID);
        $hash = $hasher->hash('SecurePass123');

        self::assertTrue($hasher->verify('SecurePass123', $hash));
        self::assertStringContainsString('argon2', $hash);
    }

    #[Test]
    public function test_password_hasher_supports_bcrypt_fallback(): void
    {
        $hasher = new PasswordHasher(algo: PASSWORD_BCRYPT);
        $hash = $hasher->hash('SecurePass123');

        self::assertTrue($hasher->verify('SecurePass123', $hash));
        self::assertMatchesRegularExpression('/^\$2[by]\$/', $hash);
    }

    #[Test]
    public function test_password_hasher_dummy_hash_exists_for_timing_attack_mitigation(): void
    {
        $hasher = new PasswordHasher();
        $dummyHash = $hasher->dummyHash();

        self::assertIsString($dummyHash);
        self::assertNotEmpty($dummyHash);
        self::assertMatchesRegularExpression('/^\$2y\$12\$/', $dummyHash);
    }

    #[Test]
    public function test_password_hasher_is_final_readonly(): void
    {
        $reflection = new ReflectionClass(PasswordHasher::class);
        self::assertTrue($reflection->isFinal(), 'PasswordHasher must be final');
        self::assertTrue($reflection->isReadonly(), 'PasswordHasher must be readonly');
    }
}
