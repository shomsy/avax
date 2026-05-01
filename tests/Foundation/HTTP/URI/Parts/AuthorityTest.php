<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\Components\HTTP\URI\Parts\Authority;
use Avax\Components\HTTP\URI\Parts\Host;
use Avax\Components\HTTP\URI\Parts\Port;
use Avax\Components\HTTP\URI\Parts\Scheme;
use Avax\Components\HTTP\URI\Parts\UserInfo;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Authority part.
 *
 * Verifies authority composition, user info rendering, and port handling.
 */
final class AuthorityTest extends TestCase
{
    // ========== HAPPY PATH: Authority with all components ==========

    public function test_it_renders_authority_with_all_components() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $port      = new Port(port: 8080, scheme: new Scheme(scheme: 'https'));
        $userInfo  = new UserInfo(user: 'user', password: 'pass');
        $authority = new Authority(host: $host, port: $port, userInfo: $userInfo);

        // Act & Assert
        self::assertSame(expected: 'user:pass@example.com:8080', actual: (string) $authority);
    }

    public function test_it_renders_authority_with_user_only() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $userInfo  = new UserInfo(user: 'user');
        $authority = new Authority(host: $host, port: null, userInfo: $userInfo);

        // Act & Assert
        self::assertSame(expected: 'user@example.com', actual: (string) $authority);
    }

    // ========== HAPPY PATH: Authority without optional components ==========

    public function test_it_renders_authority_with_host_only() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $authority = new Authority(host: $host);

        // Act & Assert
        self::assertSame(expected: 'example.com', actual: (string) $authority);
    }

    public function test_it_renders_authority_with_host_and_port() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $port      = new Port(port: 8080, scheme: new Scheme(scheme: 'https'));
        $authority = new Authority(host: $host, port: $port);

        // Act & Assert
        self::assertSame(expected: 'example.com:8080', actual: (string) $authority);
    }

    public function test_it_omits_default_port_from_output() : void
    {
        // Arrange
        $host = new Host(host: 'example.com');
        // Creating port with 443 should return null for https default
        $port      = new Port(port: 443, scheme: new Scheme(scheme: 'https'));
        $authority = new Authority(host: $host, port: $port);

        // Act & Assert
        // Since Port returns null for default, authority should not include port
        $result = (string) $authority;
        self::assertSame(expected: 'example.com', actual: $result);
    }

    // ========== COMPONENT ACCESS ==========

    public function test_it_provides_access_to_host() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $authority = new Authority(host: $host);

        // Act & Assert
        self::assertSame(expected: $host, actual: $authority->host());
        self::assertSame(expected: 'example.com', actual: (string) $authority->host());
    }

    public function test_it_provides_access_to_port() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $port      = new Port(port: 8080, scheme: new Scheme(scheme: 'https'));
        $authority = new Authority(host: $host, port: $port);

        // Act & Assert
        self::assertNotNull(actual: $authority->port());
        self::assertSame(expected: 8080, actual: $authority->port()->value());
    }

    public function test_it_returns_null_port_when_none_provided() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $authority = new Authority(host: $host);

        // Act & Assert
        self::assertNull(actual: $authority->port());
    }

    public function test_it_provides_access_to_user_info() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $userInfo  = new UserInfo(user: 'user', password: 'pass');
        $authority = new Authority(host: $host, port: null, userInfo: $userInfo);

        // Act & Assert
        self::assertNotNull(actual: $authority->userInfo());
        self::assertSame(expected: 'user:pass', actual: (string) $authority->userInfo());
    }

    public function test_it_returns_null_user_info_when_none_provided() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $authority = new Authority(host: $host);

        // Act & Assert
        self::assertNull(actual: $authority->userInfo());
    }

    // ========== PARSING: fromString factory ==========

    public function test_it_parses_authority_from_string_with_all_parts() : void
    {
        // Arrange & Act
        $authority = Authority::fromString(authority: 'user:pass@example.com:8080', scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 'example.com', actual: (string) $authority->host());
        self::assertSame(expected: 8080, actual: $authority->port()->value());
        self::assertSame(expected: 'user:pass', actual: (string) $authority->userInfo());
    }

    public function test_it_parses_authority_from_string_host_only() : void
    {
        // Arrange & Act
        $authority = Authority::fromString(authority: 'example.com', scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 'example.com', actual: (string) $authority->host());
        self::assertNull(actual: $authority->port());
        self::assertNull(actual: $authority->userInfo());
    }

    public function test_it_parses_authority_from_string_with_user_only() : void
    {
        // Arrange & Act
        $authority = Authority::fromString(authority: 'user@example.com', scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 'user', actual: (string) $authority->userInfo());
        self::assertSame(expected: 'example.com', actual: (string) $authority->host());
    }

    // ========== EDGE CASES ==========

    public function test_it_renders_ipv4_authority() : void
    {
        // Arrange
        $host      = new Host(host: '192.168.1.1');
        $authority = new Authority(host: $host);

        // Act & Assert
        self::assertSame(expected: '192.168.1.1', actual: (string) $authority);
    }

    public function test_it_throws_ipv6_authority() : void
    {
        // Note: IPv6 addresses in brackets are not supported by Host class
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: '[2001:db8::1]');
    }

    public function test_it_throws_ipv6_with_port() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: '[2001:db8::1]');
    }

    public function test_it_handles_subdomain() : void
    {
        // Arrange
        $host      = new Host(host: 'api.example.com');
        $authority = new Authority(host: $host);

        // Act & Assert
        self::assertSame(expected: 'api.example.com', actual: (string) $authority);
    }

    public function test_it_handles_user_without_password() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $userInfo  = new UserInfo(user: 'admin');
        $authority = new Authority(host: $host, port: null, userInfo: $userInfo);

        // Act & Assert
        self::assertSame(expected: 'admin@example.com', actual: (string) $authority);
    }

    public function test_it_handles_user_with_special_characters() : void
    {
        // Arrange
        $host      = new Host(host: 'example.com');
        $userInfo  = new UserInfo(user: 'user@domain', password: 'p@ss');
        $authority = new Authority(host: $host, port: null, userInfo: $userInfo);

        // Act & Assert
        self::assertStringContainsString(needle: 'user@domain:p@ss', haystack: (string) $authority);
    }
}
