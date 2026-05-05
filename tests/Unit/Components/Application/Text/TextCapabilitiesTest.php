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
    public function testCamelCase() : void
    {
        $result = Str::camel('hello_world');
        $this->assertSame('helloWorld', $result);
    }

    public function testSnakeCase() : void
    {
        $result = Str::snake('HelloWorld');
        $this->assertSame('hello_world', $result);
    }

    public function testStudlyCase() : void
    {
        $result = Str::studly('hello_world');
        $this->assertSame('HelloWorld', $result);
    }

    public function testKebabCase() : void
    {
        $result = Str::kebab('HelloWorld');
        $this->assertSame('hello-world', $result);
    }

    public function testLowerCase() : void
    {
        $result = Str::lower('HELLO');
        $this->assertSame('hello', $result);
    }

    public function testUpperCase() : void
    {
        $result = Str::upper('hello');
        $this->assertSame('HELLO', $result);
    }

    public function testHeadline() : void
    {
        $result = Str::headline('hello-world');
        $this->assertSame('Hello World', $result);
    }

    public function testContainsFound() : void
    {
        $result = Str::contains('hello world', 'world');
        $this->assertTrue($result);
    }

    public function testContainsNotFound() : void
    {
        $result = Str::contains('hello world', 'foo');
        $this->assertFalse($result);
    }

    public function testStartsWithTrue() : void
    {
        $result = Str::startsWith('hello world', 'hello');
        $this->assertTrue($result);
    }

    public function testEndsWithTrue() : void
    {
        $result = Str::endsWith('hello world', 'world');
        $this->assertTrue($result);
    }

    public function testRandomGeneratesString() : void
    {
        $result = Str::random(16);
        $this->assertSame(16, strlen($result));
    }

    public function testLimitTruncatesText() : void
    {
        $result = Str::limit('hello world', 5);
        $this->assertSame('hello...', $result);
    }

    public function testIsValidEmailValid() : void
    {
        $result = (new IsValidEmail())->execute('test@example.com');
        $this->assertTrue($result);
    }

    public function testIsValidEmailInvalid() : void
    {
        $result = (new IsValidEmail())->execute('not-an-email');
        $this->assertFalse($result);
    }

    public function testValidateUrlValid() : void
    {
        $validator = new ValidateUrl();
        $result    = $validator('https://example.com');
        $this->assertTrue($result);
    }

    public function testValidateUrlInvalid() : void
    {
        $validator = new ValidateUrl();
        $result    = $validator('not-a-url');
        $this->assertFalse($result);
    }

    public function testValidateSlugValid() : void
    {
        $validator = new ValidateSlug();
        $result    = $validator('hello-world-123');
        $this->assertTrue($result);
    }

    public function testValidateSlugInvalid() : void
    {
        $validator = new ValidateSlug();
        $result    = $validator('hello world!');
        $this->assertFalse($result);
    }

    public function testTextOfCreatesInstance() : void
    {
        $text = Text::of('hello');
        $this->assertInstanceOf(Text::class, $text);
        $this->assertSame('hello', $text->toString());
    }

    public function testTextFromNullableWithValue() : void
    {
        $text = Text::fromNullable('hello');
        $this->assertSame('hello', $text->toString());
    }

    public function testTextFromNullableWithNullUsesDefault() : void
    {
        $text = Text::fromNullable(null, 'default');
        $this->assertSame('default', $text->toString());
    }

    public function testUuidGeneration() : void
    {
        $result = Str::uuid();
        $this->assertTrue(Str::isUuid($result));
    }

    public function testIsUuidValid() : void
    {
        $result = Str::isUuid('550e8400-e29b-41d4-a716-446655440000');
        $this->assertTrue($result);
    }

    public function testIsUuidInvalid() : void
    {
        $result = Str::isUuid('not-a-uuid');
        $this->assertFalse($result);
    }
}