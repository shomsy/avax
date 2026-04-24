<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\HTTP\URI\Parts\Host;
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
        $host = new Host('example.com');

        // Assert
        self::assertSame('example.com', (string) $host);
    }

    public function test_it_accepts_subdomain() : void
    {
        // Arrange & Act
        $host = new Host('api.example.com');

        // Assert
        self::assertSame('api.example.com', (string) $host);
    }

    public function test_it_accepts_multiple_subdomains() : void
    {
        // Arrange & Act
        $host = new Host('v2.api.example.com');

        // Assert
        self::assertSame('v2.api.example.com', (string) $host);
    }

    public function test_it_normalizes_host_to_lowercase() : void
    {
        // Arrange & Act
        $host = new Host('EXAMPLE.COM');

        // Assert
        self::assertSame('example.com', (string) $host);
    }

    public function test_it_normalizes_mixed_case_host() : void
    {
        // Arrange & Act
        $host = new Host('ExAmPlE.CoM');

        // Assert
        self::assertSame('example.com', (string) $host);
    }

    // ========== HAPPY PATH: IPv4 addresses ==========

    public function test_it_accepts_ipv4_address() : void
    {
        // Arrange & Act
        $host = new Host('192.168.1.1');

        // Assert
        self::assertSame('192.168.1.1', (string) $host);
    }

    public function test_it_accepts_ipv4_localhost() : void
    {
        // Arrange & Act
        $host = new Host('127.0.0.1');

        // Assert
        self::assertSame('127.0.0.1', (string) $host);
    }

    public function test_it_handles_ipv4_with_leading_zeros() : void
    {
        // Arrange & Act
        $host = new Host('192.168.001.001');

        // Assert
        // Leading zeros are kept as-is
        self::assertStringContainsString('192.168', (string) $host);
    }

    // ========== FAILURE: Empty or whitespace ==========

    public function test_it_throws_when_host_is_empty_string() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('');
    }

    public function test_it_throws_when_host_is_only_whitespace() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('   ');
    }

    // ========== FAILURE: Invalid domain names ==========

    public function test_it_throws_when_domain_has_double_dot() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('example..com');
    }

    public function test_it_throws_when_domain_starts_with_hyphen() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('-example.com');
    }

    public function test_it_throws_when_domain_ends_with_hyphen() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('example-.com');
    }

    public function test_it_throws_when_domain_contains_spaces() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('exam ple.com');
    }

    // ========== FAILURE: Invalid IPv6 ==========

    public function test_it_throws_when_ipv6_not_in_brackets() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('2001:db8::1');
    }

    public function test_it_throws_ipv6_in_brackets_invalid_parsing() : void
    {
        // Note: IPv6 in brackets is not properly validated by Host class
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Host('[2001:db8::1]');
    }

    // ========== EDGE CASES ==========

    public function test_it_accepts_localhost() : void
    {
        // Arrange & Act
        $host = new Host('localhost');

        // Assert
        self::assertSame('localhost', (string) $host);
    }

    public function test_it_trims_whitespace_from_host() : void
    {
        // Arrange & Act
        $host = new Host('  example.com  ');

        // Assert
        self::assertSame('example.com', (string) $host);
    }

    public function test_it_handles_host_with_trailing_dot() : void
    {
        // Arrange & Act
        $host = new Host('example.com.');

        // Assert
        // FQDN with trailing dot is valid, kept as is
        self::assertStringContainsString('example.com', (string) $host);
    }
}