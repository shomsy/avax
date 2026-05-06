<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Text;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Str;
use Avax\Components\Application\Text\System\Capabilities\Validate\IsValidEmail;
use Avax\Components\Application\Text\System\Capabilities\Validate\ValidateSlug;
use Avax\Components\Application\Text\System\Capabilities\Validate\ValidateUrl;
use Avax\Components\Application\Text\System\PublicSurface\Text;
use PHPUnit\Framework\TestCase;

final class TextCapabilitiesTest extends TestCase
{
    public function test_camel_case(): void
    {
        $result = Str::camel('hello_world');
        $this->assertSame('helloWorld', $result);
    }

    public function test_snake_case(): void
    {
        $result = Str::snake('HelloWorld');
        $this->assertSame('hello_world', $result);
    }

    public function test_studly_case(): void
    {
        $result = Str::studly('hello_world');
        $this->assertSame('HelloWorld', $result);
    }

    public function test_kebab_case(): void
    {
        $result = Str::kebab('HelloWorld');
        $this->assertSame('hello-world', $result);
    }

    public function test_lower_case(): void
    {
        $result = Str::lower('HELLO');
        $this->assertSame('hello', $result);
    }

    public function test_upper_case(): void
    {
        $result = Str::upper('hello');
        $this->assertSame('HELLO', $result);
    }

    public function test_headline(): void
    {
        $result = Str::headline('hello-world');
        $this->assertSame('Hello World', $result);
    }

    public function test_contains_found(): void
    {
        $result = Str::contains('hello world', 'world');
        $this->assertTrue($result);
    }

    public function test_contains_not_found(): void
    {
        $result = Str::contains('hello world', 'foo');
        $this->assertFalse($result);
    }

    public function test_starts_with_true(): void
    {
        $result = Str::startsWith('hello world', 'hello');
        $this->assertTrue($result);
    }

    public function test_ends_with_true(): void
    {
        $result = Str::endsWith('hello world', 'world');
        $this->assertTrue($result);
    }

    public function test_random_generates_string(): void
    {
        $result = Str::random(16);
        $this->assertSame(16, strlen($result));
    }

    public function test_limit_truncates_text(): void
    {
        $result = Str::limit('hello world', 5);
        $this->assertSame('hello...', $result);
    }

    public function test_is_valid_email_valid(): void
    {
        $result = (new IsValidEmail())->execute('test@example.com');
        $this->assertTrue($result);
    }

    public function test_is_valid_email_invalid(): void
    {
        $result = (new IsValidEmail())->execute('not-an-email');
        $this->assertFalse($result);
    }

    public function test_validate_url_valid(): void
    {
        $validator = new ValidateUrl();
        $result = $validator('https://example.com');
        $this->assertTrue($result);
    }

    public function test_validate_url_invalid(): void
    {
        $validator = new ValidateUrl();
        $result = $validator('not-a-url');
        $this->assertFalse($result);
    }

    public function test_validate_slug_valid(): void
    {
        $validator = new ValidateSlug();
        $result = $validator('hello-world-123');
        $this->assertTrue($result);
    }

    public function test_validate_slug_invalid(): void
    {
        $validator = new ValidateSlug();
        $result = $validator('hello world!');
        $this->assertFalse($result);
    }

    public function test_text_of_creates_instance(): void
    {
        $text = Text::of('hello');
        $this->assertInstanceOf(Text::class, $text);
        $this->assertSame('hello', $text->toString());
    }

    public function test_text_from_nullable_with_value(): void
    {
        $text = Text::fromNullable('hello');
        $this->assertSame('hello', $text->toString());
    }

    public function test_text_from_nullable_with_null_uses_default(): void
    {
        $text = Text::fromNullable(null, 'default');
        $this->assertSame('default', $text->toString());
    }

    public function test_uuid_generation(): void
    {
        $result = Str::uuid();
        $this->assertTrue(Str::isUuid($result));
    }

    public function test_is_uuid_valid(): void
    {
        $result = Str::isUuid('550e8400-e29b-41d4-a716-446655440000');
        $this->assertTrue($result);
    }

    public function test_is_uuid_invalid(): void
    {
        $result = Str::isUuid('not-a-uuid');
        $this->assertFalse($result);
    }
}
