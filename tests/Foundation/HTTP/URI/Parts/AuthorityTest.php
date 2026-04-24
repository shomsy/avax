<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\HTTP\URI\Parts\Authority;
use Avax\HTTP\URI\Parts\Host;
use Avax\HTTP\URI\Parts\Port;
use Avax\HTTP\URI\Parts\Scheme;
use Avax\HTTP\URI\Parts\UserInfo;
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
        $host      = new Host('example.com');
        $port      = new Port(8080, new Scheme('https'));
        $userInfo  = new UserInfo('user', 'pass');
        $authority = new Authority($host, $port, $userInfo);

        // Act & Assert
        self::assertSame('user:pass@example.com:8080', (string) $authority);
    }

    public function test_it_renders_authority_with_user_only() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $userInfo  = new UserInfo('user');
        $authority = new Authority($host, null, $userInfo);

        // Act & Assert
        self::assertSame('user@example.com', (string) $authority);
    }

    // ========== HAPPY PATH: Authority without optional components ==========

    public function test_it_renders_authority_with_host_only() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $authority = new Authority($host);

        // Act & Assert
        self::assertSame('example.com', (string) $authority);
    }

    public function test_it_renders_authority_with_host_and_port() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $port      = new Port(8080, new Scheme('https'));
        $authority = new Authority($host, $port);

        // Act & Assert
        self::assertSame('example.com:8080', (string) $authority);
    }

    public function test_it_omits_default_port_from_output() : void
    {
        // Arrange
        $host = new Host('example.com');
        // Creating port with 443 should return null for https default
        $port      = new Port(443, new Scheme('https'));
        $authority = new Authority($host, $port);

        // Act & Assert
        // Since Port returns null for default, authority should not include port
        $result = (string) $authority;
        self::assertSame('example.com', $result);
    }

    // ========== COMPONENT ACCESS ==========

    public function test_it_provides_access_to_host() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $authority = new Authority($host);

        // Act & Assert
        self::assertSame($host, $authority->host());
        self::assertSame('example.com', (string) $authority->host());
    }

    public function test_it_provides_access_to_port() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $port      = new Port(8080, new Scheme('https'));
        $authority = new Authority($host, $port);

        // Act & Assert
        self::assertNotNull($authority->port());
        self::assertSame(8080, $authority->port()->value());
    }

    public function test_it_returns_null_port_when_none_provided() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $authority = new Authority($host);

        // Act & Assert
        self::assertNull($authority->port());
    }

    public function test_it_provides_access_to_user_info() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $userInfo  = new UserInfo('user', 'pass');
        $authority = new Authority($host, null, $userInfo);

        // Act & Assert
        self::assertNotNull($authority->userInfo());
        self::assertSame('user:pass', (string) $authority->userInfo());
    }

    public function test_it_returns_null_user_info_when_none_provided() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $authority = new Authority($host);

        // Act & Assert
        self::assertNull($authority->userInfo());
    }

    // ========== PARSING: fromString factory ==========

    public function test_it_parses_authority_from_string_with_all_parts() : void
    {
        // Arrange & Act
        $authority = Authority::fromString('user:pass@example.com:8080', new Scheme('https'));

        // Assert
        self::assertSame('example.com', (string) $authority->host());
        self::assertSame(8080, $authority->port()->value());
        self::assertSame('user:pass', (string) $authority->userInfo());
    }

    public function test_it_parses_authority_from_string_host_only() : void
    {
        // Arrange & Act
        $authority = Authority::fromString('example.com', new Scheme('https'));

        // Assert
        self::assertSame('example.com', (string) $authority->host());
        self::assertNull($authority->port());
        self::assertNull($authority->userInfo());
    }

    public function test_it_parses_authority_from_string_with_user_only() : void
    {
        // Arrange & Act
        $authority = Authority::fromString('user@example.com', new Scheme('https'));

        // Assert
        self::assertSame('user', (string) $authority->userInfo());
        self::assertSame('example.com', (string) $authority->host());
    }

    // ========== EDGE CASES ==========

    public function test_it_renders_ipv4_authority() : void
    {
        // Arrange
        $host      = new Host('192.168.1.1');
        $authority = new Authority($host);

        // Act & Assert
        self::assertSame('192.168.1.1', (string) $authority);
    }

    public function test_it_throws_ipv6_authority() : void
    {
        // Note: IPv6 addresses in brackets are not supported by Host class
        $this->expectException(\InvalidArgumentException::class);
        new Host('[2001:db8::1]');
    }

    public function test_it_throws_ipv6_with_port() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('[2001:db8::1]');
    }

    public function test_it_handles_subdomain() : void
    {
        // Arrange
        $host      = new Host('api.example.com');
        $authority = new Authority($host);

        // Act & Assert
        self::assertSame('api.example.com', (string) $authority);
    }

    public function test_it_handles_user_without_password() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $userInfo  = new UserInfo('admin');
        $authority = new Authority($host, null, $userInfo);

        // Act & Assert
        self::assertSame('admin@example.com', (string) $authority);
    }

    public function test_it_handles_user_with_special_characters() : void
    {
        // Arrange
        $host      = new Host('example.com');
        $userInfo  = new UserInfo('user@domain', 'p@ss');
        $authority = new Authority($host, null, $userInfo);

        // Act & Assert
        self::assertStringContainsString('user@domain:p@ss', (string) $authority);
    }
}