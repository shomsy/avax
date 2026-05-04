<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\Components\HTTP\URI\System\Capabilities\Parts\Fragment;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Fragment part.
 *
 * Verifies fragment encoding and representation.
 */
final class FragmentTest extends TestCase
{
    // ========== HAPPY PATH: Valid fragments ==========

    public function test_it_accepts_simple_fragment() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: 'section');

        // Assert
        self::assertStringContainsString(needle: 'section', haystack: (string) $fragment);
    }

    public function test_it_accepts_fragment_with_dashes() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: 'main-section');

        // Assert
        self::assertStringContainsString(needle: 'main-section', haystack: (string) $fragment);
    }

    public function test_it_accepts_fragment_with_numbers() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: 'section-1-2-3');

        // Assert
        self::assertStringContainsString(needle: 'section-1-2-3', haystack: (string) $fragment);
    }

    // ========== ENCODING: Special characters ==========

    public function test_it_encodes_spaces() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: 'my section');

        // Assert
        self::assertStringContainsString(needle: '%20', haystack: (string) $fragment);
        // Check that literal space is not in output
        $str = (string) $fragment;
        self::assertFalse(condition: str_contains($str, ' '));
    }

    public function test_it_encodes_special_characters() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: 'section!@#$%');

        // Assert
        // Special characters should be percent-encoded
        $rendered = (string) $fragment;
        self::assertStringContainsString(needle: '%', haystack: $rendered);
    }

    public function test_it_encodes_unicode_characters() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: 'café');

        // Assert
        // UTF-8 should be percent-encoded
        $rendered = (string) $fragment;
        self::assertNotSame(expected: 'café', actual: $rendered);
        self::assertStringContainsString(needle: '%', haystack: $rendered);
    }

    // ========== EDGE CASES ==========

    public function test_it_handles_empty_fragment() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: '');

        // Assert
        self::assertSame(expected: '', actual: (string) $fragment);
    }

    public function test_it_handles_fragment_with_only_numbers() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: '12345');

        // Assert
        self::assertStringContainsString(needle: '12345', haystack: (string) $fragment);
    }

    public function test_it_encodes_percentage_signs() : void
    {
        // Arrange & Act
        $fragment = new Fragment(fragment: '100%');

        // Assert
        $rendered = (string) $fragment;
        self::assertStringContainsString(needle: '%', haystack: $rendered);
    }

    // ========== REGRESSION ==========

    public function test_it_round_trips_fragment() : void
    {
        // Arrange
        $original = 'section-with-anchor';

        // Act
        $fragment = new Fragment(fragment: $original);

        // Assert
        self::assertStringContainsString(needle: $original, haystack: (string) $fragment);
    }
}
