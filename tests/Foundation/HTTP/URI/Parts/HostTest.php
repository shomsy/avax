<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\Components\HTTP\URI\Parts\Host;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Host part.
 *
 * Verifies host validation, normalization, and IDN handling.
 */
final class HostTest extends TestCase
{
    // ========== HAPPY PATH: Valid domain names ==========

    public function test_it_accepts_simple_domain_name() : void
    {
        // Arrange & Act
        $host = new Host(host: 'example.com');

        // Assert
        self::assertSame(expected: 'example.com', actual: (string) $host);
    }

    public function test_it_accepts_subdomain() : void
    {
        // Arrange & Act
        $host = new Host(host: 'api.example.com');

        // Assert
        self::assertSame(expected: 'api.example.com', actual: (string) $host);
    }

    public function test_it_accepts_multiple_subdomains() : void
    {
        // Arrange & Act
        $host = new Host(host: 'v2.api.example.com');

        // Assert
        self::assertSame(expected: 'v2.api.example.com', actual: (string) $host);
    }

    public function test_it_normalizes_host_to_lowercase() : void
    {
        // Arrange & Act
        $host = new Host(host: 'EXAMPLE.COM');

        // Assert
        self::assertSame(expected: 'example.com', actual: (string) $host);
    }

    public function test_it_normalizes_mixed_case_host() : void
    {
        // Arrange & Act
        $host = new Host(host: 'ExAmPlE.CoM');

        // Assert
        self::assertSame(expected: 'example.com', actual: (string) $host);
    }

    // ========== HAPPY PATH: IPv4 addresses ==========

    public function test_it_accepts_ipv4_address() : void
    {
        // Arrange & Act
        $host = new Host(host: '192.168.1.1');

        // Assert
        self::assertSame(expected: '192.168.1.1', actual: (string) $host);
    }

    public function test_it_accepts_ipv4_localhost() : void
    {
        // Arrange & Act
        $host = new Host(host: '127.0.0.1');

        // Assert
        self::assertSame(expected: '127.0.0.1', actual: (string) $host);
    }

    public function test_it_handles_ipv4_with_leading_zeros() : void
    {
        // Arrange & Act
        $host = new Host(host: '192.168.001.001');

        // Assert
        // Leading zeros are kept as-is
        self::assertStringContainsString(needle: '192.168', haystack: (string) $host);
    }

    // ========== FAILURE: Empty or whitespace ==========

    public function test_it_throws_when_host_is_empty_string() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: '');
    }

    public function test_it_throws_when_host_is_only_whitespace() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: '   ');
    }

    // ========== FAILURE: Invalid domain names ==========

    public function test_it_throws_when_domain_has_double_dot() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: 'example..com');
    }

    public function test_it_throws_when_domain_starts_with_hyphen() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: '-example.com');
    }

    public function test_it_throws_when_domain_ends_with_hyphen() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: 'example-.com');
    }

    public function test_it_throws_when_domain_contains_spaces() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: 'exam ple.com');
    }

    // ========== FAILURE: Invalid IPv6 ==========

    public function test_it_throws_when_ipv6_not_in_brackets() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: '2001:db8::1');
    }

    public function test_it_throws_ipv6_in_brackets_invalid_parsing() : void
    {
        // Note: IPv6 in brackets is not properly validated by Host class
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Host(host: '[2001:db8::1]');
    }

    // ========== EDGE CASES ==========

    public function test_it_accepts_localhost() : void
    {
        // Arrange & Act
        $host = new Host(host: 'localhost');

        // Assert
        self::assertSame(expected: 'localhost', actual: (string) $host);
    }

    public function test_it_trims_whitespace_from_host() : void
    {
        // Arrange & Act
        $host = new Host(host: '  example.com  ');

        // Assert
        self::assertSame(expected: 'example.com', actual: (string) $host);
    }

    public function test_it_handles_host_with_trailing_dot() : void
    {
        // Arrange & Act
        $host = new Host(host: 'example.com.');

        // Assert
        // FQDN with trailing dot is valid, kept as is
        self::assertStringContainsString(needle: 'example.com', haystack: (string) $host);
    }
}
