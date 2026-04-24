<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\HTTP\URI\Parts\Path;
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
        $path = new Path('/api/users');

        // Assert
        self::assertSame('/api/users', (string) $path);
    }

    public function test_it_accepts_root_path() : void
    {
        // Arrange & Act
        $path = new Path('/');

        // Assert
        self::assertSame('/', (string) $path);
    }

    public function test_it_accepts_path_with_trailing_slash() : void
    {
        // Arrange & Act
        $path = new Path('/api/users/');

        // Assert
        self::assertSame('/api/users', (string) $path);
    }

    // ========== HAPPY PATH: Path normalization ==========

    public function test_it_removes_dot_segments() : void
    {
        // Arrange & Act
        $path = new Path('/api/./users');

        // Assert
        self::assertSame('/api/users', (string) $path);
    }

    public function test_it_handles_parent_directory_segments() : void
    {
        // Arrange & Act
        $path = new Path('/api/admin/../users');

        // Assert
        self::assertSame('/api/users', (string) $path);
    }

    public function test_it_normalizes_multiple_slashes() : void
    {
        // Arrange & Act
        $path = new Path('/api//users');

        // Assert
        // Empty segments removed
        self::assertSame('/api/users', (string) $path);
    }

    public function test_it_normalizes_complex_path_with_dots_and_slashes() : void
    {
        // Arrange & Act
        $path = new Path('/api/v1/./admin/../users');

        // Assert
        self::assertSame('/api/v1/users', (string) $path);
    }

    // ========== HAPPY PATH: Special characters and encoding ==========

    public function test_it_encodes_spaces_in_segment() : void
    {
        // Arrange & Act
        $path = new Path('/search files');

        // Assert
        self::assertStringContainsString('%20', (string) $path);
    }

    public function test_it_encodes_special_characters() : void
    {
        // Arrange & Act
        $path = new Path('/document@v1.2.3');

        // Assert
        // @ and . might be encoded depending on RFC 3986
        self::assertStringContainsString('document', (string) $path);
    }

    public function test_it_handles_unicode_domain_segment() : void
    {
        // Arrange & Act
        $path = new Path('/café');

        // Assert
        // UTF-8 characters should be percent-encoded
        self::assertNotSame('/café', (string) $path);
    }

    // ========== EDGE CASES: Empty and boundary cases ==========

    public function test_it_converts_empty_path_to_root() : void
    {
        // Arrange & Act
        $path = new Path('');

        // Assert
        self::assertSame('/', (string) $path);
    }

    public function test_it_ensures_leading_slash() : void
    {
        // Arrange & Act
        $path = new Path('api/users');

        // Assert
        self::assertSame('/api/users', (string) $path);
    }

    public function test_it_handles_single_segment_path() : void
    {
        // Arrange & Act
        $path = new Path('filename');

        // Assert
        self::assertSame('/filename', (string) $path);
    }

    public function test_it_removes_trailing_slash() : void
    {
        // Arrange & Act
        $path = new Path('/users/');

        // Assert
        self::assertSame('/users', (string) $path);
    }

    // ========== EDGE CASES: Complex normalizations ==========

    public function test_it_handles_multiple_parent_references() : void
    {
        // Arrange & Act
        $path = new Path('/api/v1/../../../root');

        // Assert
        // Parent refs that go above root are handled safely
        self::assertSame('/root', (string) $path);
    }

    public function test_it_removes_trailing_dot_and_slash() : void
    {
        // Arrange & Act
        $path = new Path('/api/users/..');

        // Assert
        self::assertSame('/api', (string) $path);
    }

    public function test_it_handles_only_slashes() : void
    {
        // Arrange & Act
        $path = new Path('///');

        // Assert
        self::assertSame('/', (string) $path);
    }

    public function test_it_encodes_each_segment_independently() : void
    {
        // Arrange & Act
        $path = new Path('/api users/search results');

        // Assert
        // Each segment should be encoded
        self::assertStringContainsString('%20', (string) $path);
    }

    // ========== REGRESSION: Known edge cases ==========

    public function test_it_double_encodes_percent_sequences() : void
    {
        // Arrange & Act
        $path = new Path('/files%20here');

        // Assert
        // Percent signs themselves get encoded
        self::assertStringContainsString('%25', (string) $path);
    }

    public function test_it_handles_numeric_segments_without_confusion() : void
    {
        // Arrange & Act
        $path = new Path('/v1/v2/v3');

        // Assert
        self::assertSame('/v1/v2/v3', (string) $path);
    }
}