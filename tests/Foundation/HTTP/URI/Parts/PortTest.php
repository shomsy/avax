<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\HTTP\URI\Parts\Port;
use Avax\HTTP\URI\Parts\Scheme;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Port part.
 *
 * Verifies port validation and default port suppression.
 */
final class PortTest extends TestCase
{
    // ========== HAPPY PATH: Valid ports ==========

    public function test_it_accepts_valid_port_number() : void
    {
        // Arrange & Act
        $port = new Port(8080, new Scheme('https'));

        // Assert
        self::assertSame(8080, $port->value());
    }

    public function test_it_accepts_high_port_number() : void
    {
        // Arrange & Act
        $port = new Port(65535, new Scheme('https'));

        // Assert
        self::assertSame(65535, $port->value());
    }

    public function test_it_accepts_low_port_number() : void
    {
        // Arrange & Act
        $port = new Port(1, new Scheme('https'));

        // Assert
        self::assertSame(1, $port->value());
    }

    public function test_it_returns_null_for_default_http_port() : void
    {
        // Arrange & Act
        $port = new Port(80, new Scheme('http'));

        // Assert
        self::assertNull($port->value());
    }

    public function test_it_returns_null_for_default_https_port() : void
    {
        // Arrange & Act
        $port = new Port(443, new Scheme('https'));

        // Assert
        self::assertNull($port->value());
    }

    public function test_it_accepts_non_default_port_for_http() : void
    {
        // Arrange & Act
        $port = new Port(8080, new Scheme('http'));

        // Assert
        self::assertSame(8080, $port->value());
    }

    public function test_it_accepts_non_default_port_for_https() : void
    {
        // Arrange & Act
        $port = new Port(8443, new Scheme('https'));

        // Assert
        self::assertSame(8443, $port->value());
    }

    // ========== HAPPY PATH: Null port ==========

    public function test_it_handles_null_port() : void
    {
        // Arrange & Act
        $port = new Port(null, new Scheme('https'));

        // Assert
        self::assertNull($port->value());
    }

    // ========== FAILURE: Invalid ports ==========

    public function test_it_throws_when_port_is_zero() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Port(0, new Scheme('https'));
    }

    public function test_it_throws_when_port_is_negative() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Port(-1, new Scheme('https'));
    }

    public function test_it_throws_when_port_exceeds_maximum() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Port(65536, new Scheme('https'));
    }

    public function test_it_throws_when_port_way_beyond_maximum() : void
    {
        // Arrange & Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        new Port(99999, new Scheme('https'));
    }

    // ========== EDGE CASES: FTP scheme ==========

    public function test_it_handles_non_http_schemes() : void
    {
        // Arrange & Act
        $port = new Port(21, new Scheme('ftp'));

        // Assert
        // FTP doesn't have default port handling, so it should accept it
        self::assertSame(21, $port->value());
    }

    // ========== REGRESSION: Port boundary conditions ==========

    public function test_it_accepts_min_valid_port() : void
    {
        // Arrange & Act
        $port = new Port(1, new Scheme('https'));

        // Assert
        self::assertSame(1, $port->value());
    }

    public function test_it_accepts_max_valid_port() : void
    {
        // Arrange & Act
        $port = new Port(65535, new Scheme('https'));

        // Assert
        self::assertSame(65535, $port->value());
    }

    public function test_it_distinguishes_http_from_https_defaults() : void
    {
        // Arrange & Act
        $httpPort  = new Port(80, new Scheme('http'));
        $httpsPort = new Port(80, new Scheme('https'));

        // Assert
        self::assertNull($httpPort->value());
        self::assertSame(80, $httpsPort->value()); // 80 is not default for https
    }
}