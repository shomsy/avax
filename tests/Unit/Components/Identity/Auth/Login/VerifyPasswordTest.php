<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\Login\VerifyPassword;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VerifyPasswordTest extends TestCase
{
    private PasswordHasher $hasher;

    protected function setUp(): void
    {
        $this->hasher = new PasswordHasher();
    }

    #[Test]
    public function test_it_returns_true_when_password_matches_hash(): void
    {
        $passwordHash = $this->hasher->hash('SecurePass123');
        $user = new User(
            id: new UserId(1),
            email: new UserEmail('milos@example.com'),
            username: 'milos',
            passwordHash: $passwordHash,
        );

        $verifyPassword = new VerifyPassword($this->hasher);

        self::assertTrue($verifyPassword->execute($user, 'SecurePass123'));
    }

    #[Test]
    public function test_it_returns_false_when_password_does_not_match_hash(): void
    {
        $passwordHash = $this->hasher->hash('SecurePass123');
        $user = new User(
            id: new UserId(1),
            email: new UserEmail('milos@example.com'),
            username: 'milos',
            passwordHash: $passwordHash,
        );

        $verifyPassword = new VerifyPassword($this->hasher);

        self::assertFalse($verifyPassword->execute($user, 'WrongPassword'));
    }
}
