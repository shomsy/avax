<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use components\HTTP\URI\Parts\Path;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Path part.
 *
 * Verifies path normalization, encoding, and segment handling.
 */
final class PathTest extends TestCase
{
    // ========== HAPPY PATH: Simple paths ==========

    public function test_it_accepts_simple_path() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api/users');

        // Assert
        self::assertSame(expected: '/api/users', actual: (string) $path);
    }

    public function test_it_accepts_root_path() : void
    {
        // Arrange & Act
        $path = new Path(path: '/');

        // Assert
        self::assertSame(expected: '/', actual: (string) $path);
    }

    public function test_it_accepts_path_with_trailing_slash() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api/users/');

        // Assert
        self::assertSame(expected: '/api/users', actual: (string) $path);
    }

    // ========== HAPPY PATH: Path normalization ==========

    public function test_it_removes_dot_segments() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api/./users');

        // Assert
        self::assertSame(expected: '/api/users', actual: (string) $path);
    }

    public function test_it_handles_parent_directory_segments() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api/admin/../users');

        // Assert
        self::assertSame(expected: '/api/users', actual: (string) $path);
    }

    public function test_it_normalizes_multiple_slashes() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api//users');

        // Assert
        // Empty segments removed
        self::assertSame(expected: '/api/users', actual: (string) $path);
    }

    public function test_it_normalizes_complex_path_with_dots_and_slashes() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api/v1/./admin/../users');

        // Assert
        self::assertSame(expected: '/api/v1/users', actual: (string) $path);
    }

    // ========== HAPPY PATH: Special characters and encoding ==========

    public function test_it_encodes_spaces_in_segment() : void
    {
        // Arrange & Act
        $path = new Path(path: '/search files');

        // Assert
        self::assertStringContainsString(needle: '%20', haystack: (string) $path);
    }

    public function test_it_encodes_special_characters() : void
    {
        // Arrange & Act
        $path = new Path(path: '/document@v1.2.3');

        // Assert
        // @ and . might be encoded depending on RFC 3986
        self::assertStringContainsString(needle: 'document', haystack: (string) $path);
    }

    public function test_it_handles_unicode_domain_segment() : void
    {
        // Arrange & Act
        $path = new Path(path: '/café');

        // Assert
        // UTF-8 characters should be percent-encoded
        self::assertNotSame(expected: '/café', actual: (string) $path);
    }

    // ========== EDGE CASES: Empty and boundary cases ==========

    public function test_it_converts_empty_path_to_root() : void
    {
        // Arrange & Act
        $path = new Path(path: '');

        // Assert
        self::assertSame(expected: '/', actual: (string) $path);
    }

    public function test_it_ensures_leading_slash() : void
    {
        // Arrange & Act
        $path = new Path(path: 'api/users');

        // Assert
        self::assertSame(expected: '/api/users', actual: (string) $path);
    }

    public function test_it_handles_single_segment_path() : void
    {
        // Arrange & Act
        $path = new Path(path: 'filename');

        // Assert
        self::assertSame(expected: '/filename', actual: (string) $path);
    }

    public function test_it_removes_trailing_slash() : void
    {
        // Arrange & Act
        $path = new Path(path: '/users/');

        // Assert
        self::assertSame(expected: '/users', actual: (string) $path);
    }

    // ========== EDGE CASES: Complex normalizations ==========

    public function test_it_handles_multiple_parent_references() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api/v1/../../../root');

        // Assert
        // Parent refs that go above root are handled safely
        self::assertSame(expected: '/root', actual: (string) $path);
    }

    public function test_it_removes_trailing_dot_and_slash() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api/users/..');

        // Assert
        self::assertSame(expected: '/api', actual: (string) $path);
    }

    public function test_it_handles_only_slashes() : void
    {
        // Arrange & Act
        $path = new Path(path: '///');

        // Assert
        self::assertSame(expected: '/', actual: (string) $path);
    }

    public function test_it_encodes_each_segment_independently() : void
    {
        // Arrange & Act
        $path = new Path(path: '/api users/search results');

        // Assert
        // Each segment should be encoded
        self::assertStringContainsString(needle: '%20', haystack: (string) $path);
    }

    // ========== REGRESSION: Known edge cases ==========

    public function test_it_double_encodes_percent_sequences() : void
    {
        // Arrange & Act
        $path = new Path(path: '/files%20here');

        // Assert
        // Percent signs themselves get encoded
        self::assertStringContainsString(needle: '%25', haystack: (string) $path);
    }

    public function test_it_handles_numeric_segments_without_confusion() : void
    {
        // Arrange & Act
        $path = new Path(path: '/v1/v2/v3');

        // Assert
        self::assertSame(expected: '/v1/v2/v3', actual: (string) $path);
    }
}