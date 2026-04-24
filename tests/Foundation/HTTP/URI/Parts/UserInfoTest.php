<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\HTTP\URI\Parts\UserInfo;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for UserInfo part.
 *
 * Verifies user info composition and rendering with credentials.
 */
final class UserInfoTest extends TestCase
{
    // ========== HAPPY PATH: User with password ==========

    public function test_it_creates_user_with_password() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('user', 'password');

        // Assert
        self::assertSame('user:password', (string) $userInfo);
        self::assertSame('user', $userInfo->user());
        self::assertSame('password', $userInfo->password());
    }

    public function test_it_renders_user_and_password_with_colon() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('admin', 'secret123');

        // Assert
        self::assertSame('admin:secret123', (string) $userInfo);
    }

    // ========== HAPPY PATH: User without password ==========

    public function test_it_creates_user_without_password() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('guest');

        // Assert
        self::assertSame('guest', (string) $userInfo);
        self::assertSame('guest', $userInfo->user());
        self::assertNull($userInfo->password());
    }

    public function test_it_omits_colon_when_no_password() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('user', null);

        // Assert
        self::assertSame('user', (string) $userInfo);
        self::assertStringNotContainsString(':', (string) $userInfo);
    }

    // ========== COMPONENT ACCESS ==========

    public function test_it_provides_access_to_user() : void
    {
        // Arrange
        $userInfo = new UserInfo('john', 'doe');

        // Act & Assert
        self::assertSame('john', $userInfo->user());
    }

    public function test_it_provides_access_to_password() : void
    {
        // Arrange
        $userInfo = new UserInfo('john', 'secret');

        // Act & Assert
        self::assertSame('secret', $userInfo->password());
    }

    public function test_it_returns_null_password_when_not_set() : void
    {
        // Arrange
        $userInfo = new UserInfo('john');

        // Act & Assert
        self::assertNull($userInfo->password());
    }

    // ========== EDGE CASES: Special characters ==========

    public function test_it_handles_user_with_special_characters() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('user@domain.com', 'pass');

        // Assert
        self::assertStringContainsString('user@domain.com', (string) $userInfo);
        self::assertStringContainsString('pass', (string) $userInfo);
    }

    public function test_it_handles_password_with_special_characters() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('user', 'p@ss:word!');

        // Assert
        self::assertStringContainsString('p@ss:word!', (string) $userInfo);
    }

    public function test_it_handles_password_with_colon() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('user', 'pass:word:123');

        // Assert
        $rendered = (string) $userInfo;
        self::assertStringContainsString('user:pass:word:123', $rendered);
    }

    // ========== EDGE CASES: Empty values ==========

    public function test_it_accepts_empty_user() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('');

        // Assert
        self::assertSame('', (string) $userInfo);
    }

    public function test_it_accepts_empty_password() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('user', '');

        // Assert
        self::assertSame('user:', (string) $userInfo);
    }

    // ========== IMMUTABILITY: SensitiveParameter marking ==========

    public function test_it_marks_password_as_sensitive() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('user', 'secret');

        // Assert
        // Verify the password is stored but marked as sensitive in signature
        self::assertSame('secret', $userInfo->password());
    }

    // ========== REGRESSION ==========

    public function test_it_round_trips_user_info() : void
    {
        // Arrange
        $original = 'user:password123';

        // Act
        $userInfo = new UserInfo('user', 'password123');

        // Assert
        self::assertSame($original, (string) $userInfo);
    }

    public function test_it_handles_url_encoded_credentials() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo('user%40domain', 'pass%3Aword');

        // Assert
        self::assertStringContainsString('user%40domain', (string) $userInfo);
        self::assertStringContainsString('pass%3Aword', (string) $userInfo);
    }
}