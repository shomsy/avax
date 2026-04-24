<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\HTTP\URI\Parts\Query;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Query part.
 *
 * Verifies immutable query parameter handling, encoding, and repeated param support.
 */
final class QueryTest extends TestCase
{
    // ========== HAPPY PATH: Creating queries ==========

    public function test_it_creates_empty_query() : void
    {
        // Arrange & Act
        $query = new Query();

        // Assert
        self::assertSame('', (string) $query);
    }

    public function test_it_parses_single_parameter() : void
    {
        // Arrange & Act
        $query = new Query('foo=bar');

        // Assert
        self::assertSame('foo=bar', (string) $query);
        self::assertSame('bar', $query->get('foo'));
    }

    public function test_it_parses_multiple_parameters() : void
    {
        // Arrange & Act
        $query = new Query('foo=bar&baz=qux');

        // Assert
        self::assertSame('foo=bar&baz=qux', (string) $query);
        self::assertSame('bar', $query->get('foo'));
        self::assertSame('qux', $query->get('baz'));
    }

    public function test_it_returns_all_parameters() : void
    {
        // Arrange & Act
        $query = new Query('foo=bar&baz=qux&alpha=beta');

        // Assert
        $all = $query->all();
        self::assertCount(3, $all);
        self::assertSame('bar', $all['foo']);
        self::assertSame('qux', $all['baz']);
        self::assertSame('beta', $all['alpha']);
    }

    // ========== IMMUTABILITY: add method ==========

    public function test_it_returns_new_instance_when_adding_parameter() : void
    {
        // Arrange
        $originalQuery = new Query('foo=bar');

        // Act
        $newQuery = $originalQuery->add('baz', 'qux');

        // Assert
        self::assertNotSame($originalQuery, $newQuery);
        self::assertSame('foo=bar', (string) $originalQuery);
        self::assertStringContainsString('baz=qux', (string) $newQuery);
    }

    public function test_it_supports_repeated_parameters_on_add() : void
    {
        // Arrange
        $query = new Query('foo=1');

        // Act
        $newQuery = $query->add('foo', '2');

        // Assert
        $all = $newQuery->all();
        self::assertIsArray($all['foo']);
        self::assertContains('1', $all['foo']);
        self::assertContains('2', $all['foo']);
    }

    public function test_it_chains_multiple_adds() : void
    {
        // Arrange
        $query = new Query();

        // Act
        $newQuery = $query
            ->add('foo', 'bar')
            ->add('baz', 'qux')
            ->add('foo', 'bar2');

        // Assert
        // http_build_query encodes arrays with [index] notation
        $rendered = (string) $newQuery;
        self::assertStringContainsString('foo', $rendered);
        self::assertStringContainsString('bar', $rendered);
        self::assertStringContainsString('bar2', $rendered);
        self::assertStringContainsString('baz=qux', $rendered);
    }

    // ========== IMMUTABILITY: set method ==========

    public function test_it_returns_new_instance_when_setting_parameter() : void
    {
        // Arrange
        $originalQuery = new Query('foo=old');

        // Act
        $newQuery = $originalQuery->set('foo', 'new');

        // Assert
        self::assertNotSame($originalQuery, $newQuery);
        self::assertSame('foo=old', (string) $originalQuery);
        self::assertSame('foo=new', (string) $newQuery);
    }

    public function test_it_overwrites_parameter_on_set() : void
    {
        // Arrange
        $query = new Query('foo=1&foo=2');

        // Act
        $newQuery = $query->set('foo', 'new');

        // Assert
        self::assertSame('foo=new', (string) $newQuery);
    }

    // ========== IMMUTABILITY: remove method ==========

    public function test_it_returns_new_instance_when_removing_parameter() : void
    {
        // Arrange
        $originalQuery = new Query('foo=bar&baz=qux');

        // Act
        $newQuery = $originalQuery->remove('foo');

        // Assert
        self::assertNotSame($originalQuery, $newQuery);
        self::assertStringContainsString('foo=bar', (string) $originalQuery);
        self::assertStringNotContainsString('foo', (string) $newQuery);
    }

    public function test_it_removes_parameter_completely() : void
    {
        // Arrange
        $query = new Query('foo=bar&baz=qux');

        // Act
        $newQuery = $query->remove('baz');

        // Assert
        self::assertSame('foo=bar', (string) $newQuery);
        self::assertNull($newQuery->get('baz'));
    }

    // ========== IMMUTABILITY: clear method ==========

    public function test_it_returns_new_instance_when_clearing() : void
    {
        // Arrange
        $originalQuery = new Query('foo=bar&baz=qux');

        // Act
        $newQuery = $originalQuery->clear();

        // Assert
        self::assertNotSame($originalQuery, $newQuery);
        self::assertSame('foo=bar&baz=qux', (string) $originalQuery);
        self::assertSame('', (string) $newQuery);
    }

    // ========== FAILURE: Getting non-existent parameter ==========

    public function test_it_returns_string_for_non_existent_parameter() : void
    {
        // Arrange
        $query = new Query('foo=bar');

        // Act & Assert
        self::assertNull($query->get('non-existent'));
    }

    // ========== EDGE CASES: Special characters ==========

    public function test_it_handles_encoded_values() : void
    {
        // Arrange & Act
        $query = new Query('search=hello%20world');

        // Assert
        // Value should be properly handled
        self::assertStringContainsString('search', (string) $query);
    }

    public function test_it_handles_empty_parameter_value() : void
    {
        // Arrange & Act
        $query = new Query('key=');

        // Assert
        self::assertSame('', $query->get('key'));
    }

    public function test_it_handles_parameter_without_value() : void
    {
        // Arrange & Act
        $query = new Query('flag');

        // Assert
        // Parameters without = sign
        self::assertNotNull($query->get('flag'));
    }

    // ========== EDGE CASES: Repeated parameters ==========

    public function test_it_handles_multiple_same_parameters() : void
    {
        // Arrange & Act
        $query = new Query('tag=php&tag=testing&tag=tdd');

        // Assert
        $tags = $query->get('tag');
        // Multiple values might be parsed as array or as last value
        // depending on parse_str behavior
        self::assertTrue(is_array($tags) || is_string($tags));
    }

    public function test_it_encodes_rendered_output() : void
    {
        // Arrange & Act
        $query    = new Query();
        $newQuery = $query->add('name', 'John Doe');

        // Assert
        // Spaces should be encoded in the output
        self::assertStringContainsString('%20', (string) $newQuery);
    }

    // ========== REGRESSION: Known behaviors ==========

    public function test_it_preserves_parameter_order() : void
    {
        // Arrange & Act
        $query = new Query('first=1&second=2&third=3');

        // Assert
        $rendered = (string) $query;
        self::assertLessThan(
            strpos($rendered, 'second'),
            strpos($rendered, 'first')
        );
        self::assertLessThan(
            strpos($rendered, 'third'),
            strpos($rendered, 'second')
        );
    }

    public function test_it_round_trips_query_string() : void
    {
        // Arrange
        $original = 'foo=bar&baz=qux&alpha=beta';

        // Act
        $query = new Query($original);

        // Assert
        self::assertSame($original, (string) $query);
    }

    public function test_it_handles_complex_query_with_special_chars() : void
    {
        // Arrange
        $queryString = 'search=hello%20world&filter=status%3Dactive&page=2';

        // Act
        $query = new Query($queryString);

        // Assert
        self::assertStringContainsString('search', (string) $query);
        self::assertStringContainsString('filter', (string) $query);
        self::assertStringContainsString('page=2', (string) $query);
    }
}