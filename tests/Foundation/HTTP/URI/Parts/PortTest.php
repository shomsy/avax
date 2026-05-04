<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\Components\HTTP\URI\System\Capabilities\Parts\Port;
use Avax\Components\HTTP\URI\System\Capabilities\Parts\Scheme;
use InvalidArgumentException;
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
        $port = new Port(port: 8080, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 8080, actual: $port->value());
    }

    public function test_it_accepts_high_port_number() : void
    {
        // Arrange & Act
        $port = new Port(port: 65535, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 65535, actual: $port->value());
    }

    public function test_it_accepts_low_port_number() : void
    {
        // Arrange & Act
        $port = new Port(port: 1, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 1, actual: $port->value());
    }

    public function test_it_returns_null_for_default_http_port() : void
    {
        // Arrange & Act
        $port = new Port(port: 80, scheme: new Scheme(scheme: 'http'));

        // Assert
        self::assertNull(actual: $port->value());
    }

    public function test_it_returns_null_for_default_https_port() : void
    {
        // Arrange & Act
        $port = new Port(port: 443, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertNull(actual: $port->value());
    }

    public function test_it_accepts_non_default_port_for_http() : void
    {
        // Arrange & Act
        $port = new Port(port: 8080, scheme: new Scheme(scheme: 'http'));

        // Assert
        self::assertSame(expected: 8080, actual: $port->value());
    }

    public function test_it_accepts_non_default_port_for_https() : void
    {
        // Arrange & Act
        $port = new Port(port: 8443, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 8443, actual: $port->value());
    }

    // ========== HAPPY PATH: Null port ==========

    public function test_it_handles_null_port() : void
    {
        // Arrange & Act
        $port = new Port(port: null, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertNull(actual: $port->value());
    }

    // ========== FAILURE: Invalid ports ==========

    public function test_it_throws_when_port_is_zero() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Port(port: 0, scheme: new Scheme(scheme: 'https'));
    }

    public function test_it_throws_when_port_is_negative() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Port(port: -1, scheme: new Scheme(scheme: 'https'));
    }

    public function test_it_throws_when_port_exceeds_maximum() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Port(port: 65536, scheme: new Scheme(scheme: 'https'));
    }

    public function test_it_throws_when_port_way_beyond_maximum() : void
    {
        // Arrange & Act & Assert
        $this->expectException(exception: InvalidArgumentException::class);
        new Port(port: 99999, scheme: new Scheme(scheme: 'https'));
    }

    // ========== EDGE CASES: FTP scheme ==========

    public function test_it_handles_non_http_schemes() : void
    {
        // Arrange & Act
        $port = new Port(port: 21, scheme: new Scheme(scheme: 'ftp'));

        // Assert
        // FTP doesn't have default port handling, so it should accept it
        self::assertSame(expected: 21, actual: $port->value());
    }

    // ========== REGRESSION: Port boundary conditions ==========

    public function test_it_accepts_min_valid_port() : void
    {
        // Arrange & Act
        $port = new Port(port: 1, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 1, actual: $port->value());
    }

    public function test_it_accepts_max_valid_port() : void
    {
        // Arrange & Act
        $port = new Port(port: 65535, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertSame(expected: 65535, actual: $port->value());
    }

    public function test_it_distinguishes_http_from_https_defaults() : void
    {
        // Arrange & Act
        $httpPort = new Port(port: 80, scheme: new Scheme(scheme: 'http'));
        $httpsPort = new Port(port: 80, scheme: new Scheme(scheme: 'https'));

        // Assert
        self::assertNull(actual: $httpPort->value());
        self::assertSame(expected: 80, actual: $httpsPort->value()); // 80 is not default for https
    }
}
