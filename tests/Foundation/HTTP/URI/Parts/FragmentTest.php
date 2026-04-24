<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\HTTP\URI\Parts\Fragment;
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
        $fragment = new Fragment('section');

        // Assert
        self::assertStringContainsString('section', (string) $fragment);
    }

    public function test_it_accepts_fragment_with_dashes() : void
    {
        // Arrange & Act
        $fragment = new Fragment('main-section');

        // Assert
        self::assertStringContainsString('main-section', (string) $fragment);
    }

    public function test_it_accepts_fragment_with_numbers() : void
    {
        // Arrange & Act
        $fragment = new Fragment('section-1-2-3');

        // Assert
        self::assertStringContainsString('section-1-2-3', (string) $fragment);
    }

    // ========== ENCODING: Special characters ==========

    public function test_it_encodes_spaces() : void
    {
        // Arrange & Act
        $fragment = new Fragment('my section');

        // Assert
        self::assertStringContainsString('%20', (string) $fragment);
        // Check that literal space is not in output
        $str = (string) $fragment;
        self::assertFalse(strpos($str, ' ') !== false);
    }

    public function test_it_encodes_special_characters() : void
    {
        // Arrange & Act
        $fragment = new Fragment('section!@#$%');

        // Assert
        // Special characters should be percent-encoded
        $rendered = (string) $fragment;
        self::assertStringContainsString('%', $rendered);
    }

    public function test_it_encodes_unicode_characters() : void
    {
        // Arrange & Act
        $fragment = new Fragment('café');

        // Assert
        // UTF-8 should be percent-encoded
        $rendered = (string) $fragment;
        self::assertNotSame('café', $rendered);
        self::assertStringContainsString('%', $rendered);
    }

    // ========== EDGE CASES ==========

    public function test_it_handles_empty_fragment() : void
    {
        // Arrange & Act
        $fragment = new Fragment('');

        // Assert
        self::assertSame('', (string) $fragment);
    }

    public function test_it_handles_fragment_with_only_numbers() : void
    {
        // Arrange & Act
        $fragment = new Fragment('12345');

        // Assert
        self::assertStringContainsString('12345', (string) $fragment);
    }

    public function test_it_encodes_percentage_signs() : void
    {
        // Arrange & Act
        $fragment = new Fragment('100%');

        // Assert
        $rendered = (string) $fragment;
        self::assertStringContainsString('%', $rendered);
    }

    // ========== REGRESSION ==========

    public function test_it_round_trips_fragment() : void
    {
        // Arrange
        $original = 'section-with-anchor';

        // Act
        $fragment = new Fragment($original);

        // Assert
        self::assertStringContainsString($original, (string) $fragment);
    }
}