<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Application\Text;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Str text utility class.
 */
final class StrTest extends TestCase
{
    #[Test]
    public function slug_converts_string_to_url_friendly_slug() : void
    {
        $this->assertEquals(expected: 'hello-world', actual: Str::slug('Hello World'));
        $this->assertEquals(expected: 'hello-world', actual: Str::slug('hello world'));
        $this->assertEquals(expected: 'hello_world', actual: Str::slug('Hello World', '_'));
    }

    #[Test]
    public function slug_handles_special_characters() : void
    {
        $this->assertEquals(expected: 'hello-world', actual: Str::slug('Hello!@#World'));
        $this->assertEquals(expected: '', actual: Str::slug('!@#$%'));
    }

    #[Test]
    public function camel_converts_string_to_camel_case() : void
    {
        $this->assertEquals(expected: 'helloWorld', actual: Str::camel('hello world'));
        $this->assertEquals(expected: 'helloWorld', actual: Str::camel('hello-world'));
    }

    #[Test]
    public function snake_converts_string_to_snake_case() : void
    {
        $this->assertEquals(expected: 'hello_world', actual: Str::snake('helloWorld'));
        $this->assertEquals(expected: 'hello_world', actual: Str::snake('HelloWorld'));
        $this->assertEquals(expected: 'hello_world', actual: Str::snake('Hello World'));
    }

    #[Test]
    public function snake_respects_custom_delimiter() : void
    {
        $this->assertEquals(expected: 'hello/world', actual: Str::snake('helloWorld', '/'));
    }

    #[Test]
    public function studly_converts_string_to_pascal_case() : void
    {
        $this->assertEquals(expected: 'HelloWorld', actual: Str::studly('hello world'));
        $this->assertEquals(expected: 'HelloWorld', actual: Str::studly('hello-world'));
        $this->assertEquals(expected: 'HelloWorld', actual: Str::studly('hello_world'));
    }

    #[Test]
    public function kebab_converts_string_to_kebab_case() : void
    {
        $this->assertEquals(expected: 'hello-world', actual: Str::kebab('hello world'));
        $this->assertEquals(expected: 'hello-world', actual: Str::kebab('helloWorld'));
    }

    #[Test]
    public function headline_converts_string_to_title_case() : void
    {
        $this->assertEquals(expected: 'Hello World', actual: Str::headline('helloWorld'));
        $this->assertEquals(expected: 'Hello World', actual: Str::headline('hello_world'));
        $this->assertEquals(expected: 'Hello World', actual: Str::headline('hello-world'));
    }

    #[Test]
    public function plural_converts_word_to_plural() : void
    {
        $this->assertEquals(expected: 'people', actual: Str::plural('person'));
        $this->assertEquals(expected: 'children', actual: Str::plural('child'));
        $this->assertEquals(expected: 'users', actual: Str::plural('user'));
        $this->assertEquals(expected: 'categories', actual: Str::plural('category'));
    }

    #[Test]
    public function plural_returns_singular_for_count_one() : void
    {
        $this->assertEquals(expected: 'person', actual: Str::plural('person', 1));
        $this->assertEquals(expected: 'user', actual: Str::plural('user', 1));
    }

    #[Test]
    public function singular_converts_word_to_singular() : void
    {
        $this->assertEquals(expected: 'person', actual: Str::singular('people'));
        $this->assertEquals(expected: 'child', actual: Str::singular('children'));
        $this->assertEquals(expected: 'user', actual: Str::singular('users'));
        $this->assertEquals(expected: 'category', actual: Str::singular('categories'));
    }

    #[Test]
    public function contains_checks_string_contains_needle() : void
    {
        $this->assertTrue(Str::contains('Hello World', 'World'));
        $this->assertFalse(Str::contains('Hello World', 'world'));
        $this->assertTrue(Str::contains('Hello World', 'world', caseSensitive: false));
    }

    #[Test]
    public function contains_works_with_array_of_needles() : void
    {
        $this->assertTrue(Str::contains('Hello World', ['foo', 'World']));
        $this->assertFalse(Str::contains('Hello World', ['foo', 'bar']));
    }

    #[Test]
    public function starts_with_checks_string_prefix() : void
    {
        $this->assertTrue(Str::startsWith('Hello World', 'Hello'));
        $this->assertFalse(Str::startsWith('Hello World', 'World'));
        $this->assertTrue(Str::startsWith('Hello World', ['World', 'Hello']));
    }

    #[Test]
    public function ends_with_checks_string_suffix() : void
    {
        $this->assertTrue(Str::endsWith('Hello World', 'World'));
        $this->assertFalse(Str::endsWith('Hello World', 'Hello'));
        $this->assertTrue(Str::endsWith('Hello World', ['Hello', 'World']));
    }

    #[Test]
    public function limit_trims_string_to_length() : void
    {
        $this->assertEquals(expected: 'Hello...', actual: Str::limit('Hello World', 5));
        $this->assertEquals(expected: 'Hello World', actual: Str::limit('Hello World', 20));
        $this->assertEquals(expected: 'Hello>>>', actual: Str::limit('Hello World', 5, '>>>'));
    }

    #[Test]
    public function excerpt_creates_word_boundary_excerpt() : void
    {
        $text = 'The quick brown fox jumps over the lazy dog';
        $excerpt = Str::excerpt($text, 15);

        $this->assertLessThanOrEqual(expected: 18, actual: strlen($excerpt)); // 15 + '...'
        $this->assertStringEndsWith('...', $excerpt);
    }

    #[Test]
    public function excerpt_returns_full_string_if_shorter_than_length() : void
    {
        $text = 'Short text';
        $this->assertEquals(expected: 'Short text', actual: Str::excerpt($text, 100));
    }

    #[Test]
    public function random_generates_string_of_specified_length() : void
    {
        $random = Str::random(16);
        $this->assertEquals(expected: 16, actual: strlen($random));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $random);
    }

    #[Test]
    public function random_generates_different_strings() : void
    {
        $first = Str::random(32);
        $second = Str::random(32);
        $this->assertNotEquals(expected: $first, actual: $second);
    }

    #[Test]
    public function uuid_generates_valid_uuid_v4() : void
    {
        $uuid = Str::uuid();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid,
        );
    }

    #[Test]
    public function is_uuid_validates_uuid_format() : void
    {
        $this->assertTrue(Str::isUuid('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertTrue(Str::isUuid('f47ac10b-58cc-4372-a567-0e02b2c3d479'));
        $this->assertFalse(Str::isUuid('not-a-uuid'));
        $this->assertFalse(Str::isUuid('550e8400-e29b-41d4-a716'));
    }
}
