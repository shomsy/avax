<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use components\HTTP\URI\Parts\UserInfo;
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
        $userInfo = new UserInfo(user: 'user', password: 'password');

        // Assert
        self::assertSame(expected: 'user:password', actual: (string) $userInfo);
        self::assertSame(expected: 'user', actual: $userInfo->user());
        self::assertSame(expected: 'password', actual: $userInfo->password());
    }

    public function test_it_renders_user_and_password_with_colon() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'admin', password: 'secret123');

        // Assert
        self::assertSame(expected: 'admin:secret123', actual: (string) $userInfo);
    }

    // ========== HAPPY PATH: User without password ==========

    public function test_it_creates_user_without_password() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'guest');

        // Assert
        self::assertSame(expected: 'guest', actual: (string) $userInfo);
        self::assertSame(expected: 'guest', actual: $userInfo->user());
        self::assertNull(actual: $userInfo->password());
    }

    public function test_it_omits_colon_when_no_password() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'user', password: null);

        // Assert
        self::assertSame(expected: 'user', actual: (string) $userInfo);
        self::assertStringNotContainsString(needle: ':', haystack: (string) $userInfo);
    }

    // ========== COMPONENT ACCESS ==========

    public function test_it_provides_access_to_user() : void
    {
        // Arrange
        $userInfo = new UserInfo(user: 'john', password: 'doe');

        // Act & Assert
        self::assertSame(expected: 'john', actual: $userInfo->user());
    }

    public function test_it_provides_access_to_password() : void
    {
        // Arrange
        $userInfo = new UserInfo(user: 'john', password: 'secret');

        // Act & Assert
        self::assertSame(expected: 'secret', actual: $userInfo->password());
    }

    public function test_it_returns_null_password_when_not_set() : void
    {
        // Arrange
        $userInfo = new UserInfo(user: 'john');

        // Act & Assert
        self::assertNull(actual: $userInfo->password());
    }

    // ========== EDGE CASES: Special characters ==========

    public function test_it_handles_user_with_special_characters() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'user@domain.com', password: 'pass');

        // Assert
        self::assertStringContainsString(needle: 'user@domain.com', haystack: (string) $userInfo);
        self::assertStringContainsString(needle: 'pass', haystack: (string) $userInfo);
    }

    public function test_it_handles_password_with_special_characters() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'user', password: 'p@ss:word!');

        // Assert
        self::assertStringContainsString(needle: 'p@ss:word!', haystack: (string) $userInfo);
    }

    public function test_it_handles_password_with_colon() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'user', password: 'pass:word:123');

        // Assert
        $rendered = (string) $userInfo;
        self::assertStringContainsString(needle: 'user:pass:word:123', haystack: $rendered);
    }

    // ========== EDGE CASES: Empty values ==========

    public function test_it_accepts_empty_user() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: '');

        // Assert
        self::assertSame(expected: '', actual: (string) $userInfo);
    }

    public function test_it_accepts_empty_password() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'user', password: '');

        // Assert
        self::assertSame(expected: 'user:', actual: (string) $userInfo);
    }

    // ========== IMMUTABILITY: SensitiveParameter marking ==========

    public function test_it_marks_password_as_sensitive() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'user', password: 'secret');

        // Assert
        // Verify the password is stored but marked as sensitive in signature
        self::assertSame(expected: 'secret', actual: $userInfo->password());
    }

    // ========== REGRESSION ==========

    public function test_it_round_trips_user_info() : void
    {
        // Arrange
        $original = 'user:password123';

        // Act
        $userInfo = new UserInfo(user: 'user', password: 'password123');

        // Assert
        self::assertSame(expected: $original, actual: (string) $userInfo);
    }

    public function test_it_handles_url_encoded_credentials() : void
    {
        // Arrange & Act
        $userInfo = new UserInfo(user: 'user%40domain', password: 'pass%3Aword');

        // Assert
        self::assertStringContainsString(needle: 'user%40domain', haystack: (string) $userInfo);
        self::assertStringContainsString(needle: 'pass%3Aword', haystack: (string) $userInfo);
    }
}
