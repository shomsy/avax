<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\HTTP\URI\Parts\Scheme;
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
        $scheme = new Scheme('http');

        // Assert
        self::assertSame('http', (string) $scheme);
    }

    public function test_it_accepts_https_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme('https');

        // Assert
        self::assertSame('https', (string) $scheme);
    }

    public function test_it_accepts_ftp_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme('ftp');

        // Assert
        self::assertSame('ftp', (string) $scheme);
    }

    public function test_it_accepts_file_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme('file');

        // Assert
        self::assertSame('file', (string) $scheme);
    }

    public function test_it_normalizes_scheme_to_lowercase() : void
    {
        // Arrange & Act
        $scheme = new Scheme('HTTPS');

        // Assert
        self::assertSame('https', (string) $scheme);
    }

    public function test_it_normalizes_mixed_case_scheme() : void
    {
        // Arrange & Act
        $scheme = new Scheme('HtTpS');

        // Assert
        self::assertSame('https', (string) $scheme);
    }

    // ========== FAILURE: Invalid schemes ==========

    public function test_it_throws_when_scheme_is_empty() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Scheme('');
    }

    public function test_it_throws_when_scheme_is_not_allowed() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Scheme('gopher');
    }

    public function test_it_throws_when_scheme_is_unknown_protocol() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Scheme('custom-protocol');
    }

    public function test_it_throws_when_scheme_contains_invalid_characters() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Scheme('http!');
    }

    // ========== PORT DEFAULTS ==========

    public function test_it_recognizes_http_default_port_80() : void
    {
        // Arrange
        $scheme = new Scheme('http');

        // Act & Assert
        self::assertTrue($scheme->isDefaultPort(80));
    }

    public function test_it_recognizes_https_default_port_443() : void
    {
        // Arrange
        $scheme = new Scheme('https');

        // Act & Assert
        self::assertTrue($scheme->isDefaultPort(443));
    }

    public function test_it_rejects_wrong_default_port_for_http() : void
    {
        // Arrange
        $scheme = new Scheme('http');

        // Act & Assert
        self::assertFalse($scheme->isDefaultPort(443));
    }

    public function test_it_rejects_wrong_default_port_for_https() : void
    {
        // Arrange
        $scheme = new Scheme('https');

        // Act & Assert
        self::assertFalse($scheme->isDefaultPort(80));
    }

    public function test_it_rejects_non_standard_port_as_default() : void
    {
        // Arrange
        $scheme = new Scheme('https');

        // Act & Assert
        self::assertFalse($scheme->isDefaultPort(8443));
    }
}