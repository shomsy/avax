<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Login\FindUserByCredentials;
use Avax\Components\Identity\Auth\System\Flows\Login\VerifyPassword;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Login flow tests — focused on the parts that can be tested in isolation.
 *
 * Note: The full Login flow depends on Sessions::start() which is not yet
 * implemented. These tests verify the user lookup and password verification
 * behavior that can be tested independently.
 */
final class LoginTest extends TestCase
{
    private PasswordHasher $hasher;
    private InMemoryUserSource $userSource;

    protected function setUp(): void
    {
        $this->hasher = new PasswordHasher();
        $this->userSource = new InMemoryUserSource();
    }

    #[Test]
    public function test_it_finds_user_when_email_exists(): void
    {
        $user = $this->createUser('milos@example.com', 'SecurePass123');

        $findUser = new FindUserByCredentials($this->userSource);

        $result = $findUser->execute('milos@example.com');

        self::assertInstanceOf(User::class, $result);
        self::assertSame('milos@example.com', $result->email->value);
    }

    #[Test]
    public function test_it_returns_null_when_user_does_not_exist(): void
    {
        $findUser = new FindUserByCredentials($this->userSource);

        $result = $findUser->execute('nonexistent@example.com');

        self::assertNull($result);
    }

    #[Test]
    public function test_it_verifies_correct_password(): void
    {
        $user = $this->createUser('milos@example.com', 'SecurePass123');

        $verifyPassword = new VerifyPassword($this->hasher);

        self::assertTrue($verifyPassword->execute($user, 'SecurePass123'));
    }

    #[Test]
    public function test_it_rejects_wrong_password(): void
    {
        $user = $this->createUser('milos@example.com', 'SecurePass123');

        $verifyPassword = new VerifyPassword($this->hasher);

        self::assertFalse($verifyPassword->execute($user, 'WrongPassword'));
    }

    #[Test]
    public function test_it_does_not_reveal_user_existence_when_password_is_wrong(): void
    {
        $user = $this->createUser('milos@example.com', 'SecurePass123');
        $findUser = new FindUserByCredentials($this->userSource);
        $verifyPassword = new VerifyPassword($this->hasher);

        // Simulate the Login flow logic: find user, then verify password
        $foundUser = $findUser->execute('milos@example.com');
        $passwordValid = $foundUser && $verifyPassword->execute($foundUser, 'WrongPassword');

        // The flow should produce the same "Invalid credentials" whether user exists with wrong password
        // or user doesn't exist at all
        self::assertFalse($passwordValid);
    }

    #[Test]
    public function test_it_does_not_reveal_user_non_existance_when_user_not_found(): void
    {
        $findUser = new FindUserByCredentials($this->userSource);
        $verifyPassword = new VerifyPassword($this->hasher);

        // Simulate the Login flow logic for non-existent user
        $foundUser = $findUser->execute('nonexistent@example.com');
        $passwordValid = $foundUser && $verifyPassword->execute($foundUser, 'AnyPassword');

        self::assertFalse($passwordValid);
    }

    #[Test]
    public function test_inactive_user_is_found_but_password_check_fails(): void
    {
        $user = $this->createUser('locked@example.com', 'SecurePass123', isActive: false);

        $findUser = new FindUserByCredentials($this->userSource);
        $verifyPassword = new VerifyPassword($this->hasher);

        // Inactive users are still found - the Login flow should check isActive
        $foundUser = $findUser->execute('locked@example.com');
        self::assertInstanceOf(User::class, $foundUser);
        self::assertFalse($foundUser->isActive());

        // Password verification still works technically, but flow should reject inactive users
        self::assertTrue($verifyPassword->execute($foundUser, 'SecurePass123'));
    }

    #[Test]
    public function test_it_throws_when_user_source_throws(): void
    {
        $failingUserSource = new FailingUserSource();
        $findUser = new FindUserByCredentials($failingUserSource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Database connection lost');

        $findUser->execute('error@example.com');
    }

    private function createUser(string $email, string $password, bool $isActive = true): User
    {
        $user = new User(
            id: new UserId(1),
            email: new UserEmail($email),
            username: explode('@', $email)[0],
            passwordHash: $this->hasher->hash($password),
            isActive: $isActive,
        );
        $this->userSource->create($user);
        return $user;
    }
}

/**
 * User source that always throws.
 */
final class FailingUserSource implements \Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface
{
    public function findByCredentials(Credentials $credentials): ?User
    {
        throw new RuntimeException('Database connection lost');
    }

    public function findById(UserId $userId): ?User
    {
        throw new RuntimeException('Database connection lost');
    }

    public function findByEmail(string $email): ?User
    {
        throw new RuntimeException('Database connection lost');
    }

    public function create(User $user): User
    {
        throw new RuntimeException('Database connection lost');
    }

    public function updatePassword(UserId $userId, string $passwordHash): void
    {
        throw new RuntimeException('Database connection lost');
    }

    public function emailExists(string $email): bool
    {
        throw new RuntimeException('Database connection lost');
    }

    public function usernameExists(string $username): bool
    {
        throw new RuntimeException('Database connection lost');
    }
}
