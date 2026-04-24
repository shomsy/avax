<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Request\System\Capability\Input;

use Avax\HTTP\Request\System\Capability\Input\JsonBodyParser;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class JsonBodyParserTest extends TestCase
{
    private JsonBodyParser $parser;

    public function test_parse_returns_array_from_valid_json() : void
    {
        $result = $this->parser->parse('{"foo": "bar", "num": 42}');

        $this->assertSame(expected: ['foo' => 'bar', 'num' => 42], actual: $result);
    }

    public function test_parse_returns_empty_array_for_empty_string() : void
    {
        $result = $this->parser->parse('');

        $this->assertSame(expected: [], actual: $result);
    }

    public function test_parse_throws_for_invalid_json() : void
    {
        $this->expectException(RuntimeException::class);

        $this->parser->parse('{invalid json}');
    }

    public function test_parse_handles_nested_json() : void
    {
        $json   = '{"user": {"name": "John", "address": {"city": "NYC"}}}';
        $result = $this->parser->parse($json);

        $this->assertIsArray(actual: $result);
        $this->assertArrayHasKey(key: 'user', array: $result);
    }

    public function test_parse_handles_json_array() : void
    {
        $json   = '[1, 2, 3, "four"]';
        $result = $this->parser->parse($json);

        $this->assertSame(expected: [1, 2, 3, 'four'], actual: $result);
    }

    public function test_get_nested_value_returns_value() : void
    {
        $data = ['user' => ['name' => 'John']];

        $result = $this->parser->getNestedValue($data, 'user.name');

        $this->assertSame(expected: 'John', actual: $result);
    }

    public function test_get_nested_value_returns_default_when_missing() : void
    {
        $data = ['user' => ['name' => 'John']];

        $result = $this->parser->getNestedValue($data, 'user.email', 'default@example.com');

        $this->assertSame(expected: 'default@example.com', actual: $result);
    }

    protected function setUp() : void
    {
        $this->parser = new JsonBodyParser();
    }
}
