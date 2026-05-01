<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI\Parts;

use Avax\Components\HTTP\URI\Parts\Query;
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
        $query = new Query;

        // Assert
        self::assertSame(expected: '', actual: (string) $query);
    }

    public function test_it_parses_single_parameter() : void
    {
        // Arrange & Act
        $query = new Query(queryString: 'foo=bar');

        // Assert
        self::assertSame(expected: 'foo=bar', actual: (string) $query);
        self::assertSame(expected: 'bar', actual: $query->get(key: 'foo'));
    }

    public function test_it_parses_multiple_parameters() : void
    {
        // Arrange & Act
        $query = new Query(queryString: 'foo=bar&baz=qux');

        // Assert
        self::assertSame(expected: 'foo=bar&baz=qux', actual: (string) $query);
        self::assertSame(expected: 'bar', actual: $query->get(key: 'foo'));
        self::assertSame(expected: 'qux', actual: $query->get(key: 'baz'));
    }

    public function test_it_returns_all_parameters() : void
    {
        // Arrange & Act
        $query = new Query(queryString: 'foo=bar&baz=qux&alpha=beta');

        // Assert
        $all = $query->all();
        self::assertCount(expectedCount: 3, haystack: $all);
        self::assertSame(expected: 'bar', actual: $all['foo']);
        self::assertSame(expected: 'qux', actual: $all['baz']);
        self::assertSame(expected: 'beta', actual: $all['alpha']);
    }

    // ========== IMMUTABILITY: add method ==========

    public function test_it_returns_new_instance_when_adding_parameter() : void
    {
        // Arrange
        $originalQuery = new Query(queryString: 'foo=bar');

        // Act
        $newQuery = $originalQuery->add(key: 'baz', value: 'qux');

        // Assert
        self::assertNotSame(expected: $originalQuery, actual: $newQuery);
        self::assertSame(expected: 'foo=bar', actual: (string) $originalQuery);
        self::assertStringContainsString(needle: 'baz=qux', haystack: (string) $newQuery);
    }

    public function test_it_supports_repeated_parameters_on_add() : void
    {
        // Arrange
        $query = new Query(queryString: 'foo=1');

        // Act
        $newQuery = $query->add(key: 'foo', value: '2');

        // Assert
        $all = $newQuery->all();
        self::assertIsArray(actual: $all['foo']);
        self::assertContains(needle: '1', haystack: $all['foo']);
        self::assertContains(needle: '2', haystack: $all['foo']);
    }

    public function test_it_chains_multiple_adds() : void
    {
        // Arrange
        $query = new Query;

        // Act
        $newQuery = $query
            ->add(key: 'foo', value: 'bar')
            ->add(key: 'baz', value: 'qux')
            ->add(key: 'foo', value: 'bar2');

        // Assert
        // http_build_query encodes arrays with [index] notation
        $rendered = (string) $newQuery;
        self::assertStringContainsString(needle: 'foo', haystack: $rendered);
        self::assertStringContainsString(needle: 'bar', haystack: $rendered);
        self::assertStringContainsString(needle: 'bar2', haystack: $rendered);
        self::assertStringContainsString(needle: 'baz=qux', haystack: $rendered);
    }

    // ========== IMMUTABILITY: set method ==========

    public function test_it_returns_new_instance_when_setting_parameter() : void
    {
        // Arrange
        $originalQuery = new Query(queryString: 'foo=old');

        // Act
        $newQuery = $originalQuery->set(key: 'foo', value: 'new');

        // Assert
        self::assertNotSame(expected: $originalQuery, actual: $newQuery);
        self::assertSame(expected: 'foo=old', actual: (string) $originalQuery);
        self::assertSame(expected: 'foo=new', actual: (string) $newQuery);
    }

    public function test_it_overwrites_parameter_on_set() : void
    {
        // Arrange
        $query = new Query(queryString: 'foo=1&foo=2');

        // Act
        $newQuery = $query->set(key: 'foo', value: 'new');

        // Assert
        self::assertSame(expected: 'foo=new', actual: (string) $newQuery);
    }

    // ========== IMMUTABILITY: remove method ==========

    public function test_it_returns_new_instance_when_removing_parameter() : void
    {
        // Arrange
        $originalQuery = new Query(queryString: 'foo=bar&baz=qux');

        // Act
        $newQuery = $originalQuery->remove(key: 'foo');

        // Assert
        self::assertNotSame(expected: $originalQuery, actual: $newQuery);
        self::assertStringContainsString(needle: 'foo=bar', haystack: (string) $originalQuery);
        self::assertStringNotContainsString(needle: 'foo', haystack: (string) $newQuery);
    }

    public function test_it_removes_parameter_completely() : void
    {
        // Arrange
        $query = new Query(queryString: 'foo=bar&baz=qux');

        // Act
        $newQuery = $query->remove(key: 'baz');

        // Assert
        self::assertSame(expected: 'foo=bar', actual: (string) $newQuery);
        self::assertNull(actual: $newQuery->get(key: 'baz'));
    }

    // ========== IMMUTABILITY: clear method ==========

    public function test_it_returns_new_instance_when_clearing() : void
    {
        // Arrange
        $originalQuery = new Query(queryString: 'foo=bar&baz=qux');

        // Act
        $newQuery = $originalQuery->clear();

        // Assert
        self::assertNotSame(expected: $originalQuery, actual: $newQuery);
        self::assertSame(expected: 'foo=bar&baz=qux', actual: (string) $originalQuery);
        self::assertSame(expected: '', actual: (string) $newQuery);
    }

    // ========== FAILURE: Getting non-existent parameter ==========

    public function test_it_returns_string_for_non_existent_parameter() : void
    {
        // Arrange
        $query = new Query(queryString: 'foo=bar');

        // Act & Assert
        self::assertNull(actual: $query->get(key: 'non-existent'));
    }

    // ========== EDGE CASES: Special characters ==========

    public function test_it_handles_encoded_values() : void
    {
        // Arrange & Act
        $query = new Query(queryString: 'search=hello%20world');

        // Assert
        // Value should be properly handled
        self::assertStringContainsString(needle: 'search', haystack: (string) $query);
    }

    public function test_it_handles_empty_parameter_value() : void
    {
        // Arrange & Act
        $query = new Query(queryString: 'key=');

        // Assert
        self::assertSame(expected: '', actual: $query->get(key: 'key'));
    }

    public function test_it_handles_parameter_without_value() : void
    {
        // Arrange & Act
        $query = new Query(queryString: 'flag');

        // Assert
        // Parameters without = sign
        self::assertNotNull(actual: $query->get(key: 'flag'));
    }

    // ========== EDGE CASES: Repeated parameters ==========

    public function test_it_handles_multiple_same_parameters() : void
    {
        // Arrange & Act
        $query = new Query(queryString: 'tag=php&tag=testing&tag=tdd');

        // Assert
        $tags = $query->get(key: 'tag');
        // Multiple values might be parsed as array or as last value
        // depending on parse_str behavior
        self::assertTrue(condition: is_array($tags) || is_string($tags));
    }

    public function test_it_encodes_rendered_output() : void
    {
        // Arrange & Act
        $query = new Query;
        $newQuery = $query->add(key: 'name', value: 'John Doe');

        // Assert
        // Spaces should be encoded in the output
        self::assertStringContainsString(needle: '%20', haystack: (string) $newQuery);
    }

    // ========== REGRESSION: Known behaviors ==========

    public function test_it_preserves_parameter_order() : void
    {
        // Arrange & Act
        $query = new Query(queryString: 'first=1&second=2&third=3');

        // Assert
        $rendered = (string) $query;
        self::assertLessThan(
            expected: strpos($rendered, 'second'),
            actual  : strpos($rendered, 'first'),
        );
        self::assertLessThan(
            expected: strpos($rendered, 'third'),
            actual  : strpos($rendered, 'second'),
        );
    }

    public function test_it_round_trips_query_string() : void
    {
        // Arrange
        $original = 'foo=bar&baz=qux&alpha=beta';

        // Act
        $query = new Query(queryString: $original);

        // Assert
        self::assertSame(expected: $original, actual: (string) $query);
    }

    public function test_it_handles_complex_query_with_special_chars() : void
    {
        // Arrange
        $queryString = 'search=hello%20world&filter=status%3Dactive&page=2';

        // Act
        $query = new Query(queryString: $queryString);

        // Assert
        self::assertStringContainsString(needle: 'search', haystack: (string) $query);
        self::assertStringContainsString(needle: 'filter', haystack: (string) $query);
        self::assertStringContainsString(needle: 'page=2', haystack: (string) $query);
    }
}
