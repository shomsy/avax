<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\Components\HTTP\URI\System\Capabilities\Parts\Scheme;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Scheme part.
 *
 * Verifies scheme validation and normalization.
 */
final class SchemeTest extends TestCase
{
    // ========== HAPPY PATH: Valid schemes ==========

    public function test_it_accepts_http_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme(scheme: 'http');

        // Assert
        self::assertSame(expected: 'http', actual: (string) $scheme);
    }

    public function test_it_accepts_https_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme(scheme: 'https');

        // Assert
        self::assertSame(expected: 'https', actual: (string) $scheme);
    }

    public function test_it_accepts_ftp_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme(scheme: 'ftp');

        // Assert
        self::assertSame(expected: 'ftp', actual: (string) $scheme);
    }

    public function test_it_accepts_file_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme(scheme: 'file');

        // Assert
        self::assertSame(expected: 'file', actual: (string) $scheme);
    }

    public function test_it_normalizes_scheme_to_lowercase() : void
    {
        // Arrange & Act
        $scheme = new Scheme(scheme: 'HTTPS');

        // Assert
        self::assertSame(expected: 'https', actual: (string) $scheme);
    }

    public function test_it_normalizes_mixed_case_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme(scheme: 'HtTpS');

        // Assert
        self::assertSame(expected: 'https', actual: (string) $scheme);
    }

    // ========== FAILURE: Invalid schemes ==========

    public function test_it_throws_when_scheme_is_empty() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Scheme(scheme: '');
    }

    public function test_it_throws_when_scheme_is_not_allowed() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Scheme(scheme: 'gopher');
    }

    public function test_it_throws_when_scheme_is_unknown_protocol() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Scheme(scheme: 'custom-protocol');
    }

    public function test_it_throws_when_scheme_contains_invalid_characters() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Scheme(scheme: 'http!');
    }

    // ========== PORT DEFAULTS ==========

    public function test_it_recognizes_http_default_port_80() : void
    {
        // Arrange
        $scheme = new Scheme(scheme: 'http');

        // Act & Assert
        self::assertTrue(condition: $scheme->isDefaultPort(port: 80));
    }

    public function test_it_recognizes_https_default_port_443() : void
    {
        // Arrange
        $scheme = new Scheme(scheme: 'https');

        // Act & Assert
        self::assertTrue(condition: $scheme->isDefaultPort(port: 443));
    }

    public function test_it_rejects_wrong_default_port_for_http() : void
    {
        // Arrange
        $scheme = new Scheme(scheme: 'http');

        // Act & Assert
        self::assertFalse(condition: $scheme->isDefaultPort(port: 443));
    }

    public function test_it_rejects_wrong_default_port_for_https() : void
    {
        // Arrange
        $scheme = new Scheme(scheme: 'https');

        // Act & Assert
        self::assertFalse(condition: $scheme->isDefaultPort(port: 80));
    }

    public function test_it_rejects_non_standard_port_as_default() : void
    {
        // Arrange
        $scheme = new Scheme(scheme: 'https');

        // Act & Assert
        self::assertFalse(condition: $scheme->isDefaultPort(port: 8443));
    }
}
