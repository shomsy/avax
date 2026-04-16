<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Response\Classes\Stream;

class BodyParsingTest extends TestCase
{
    public function test_parse_json_body_returns_array_for_json_object()
    {
        $parser = new ParseJsonBody();
        $this->assertEquals(expected: ['foo' => 'bar'], actual: $parser->execute(content: '{"foo": "bar"}'));
    }

    public function test_parse_json_body_returns_array_for_json_array()
    {
        $parser = new ParseJsonBody();
        $this->assertEquals(expected: ['a', 'b'], actual: $parser->execute(content: '["a", "b"]'));
    }

    public function test_parse_json_body_returns_null_for_empty_content()
    {
        $parser = new ParseJsonBody();
        $this->assertNull(actual: $parser->execute(content: ''));
        $this->assertNull(actual: $parser->execute(content: '   '));
    }

    public function test_parse_json_body_returns_null_for_invalid_json()
    {
        $parser = new ParseJsonBody();
        $this->assertNull(actual: $parser->execute(content: 'invalid-json'));
    }

    public function test_parse_form_body_parses_urlencoded_content()
    {
        $parser = new ParseFormBody();
        $this->assertEquals(expected: ['foo' => 'bar', 'baz' => '1'], actual: $parser->execute(content: 'foo=bar&baz=1'));
    }

    public function test_content_preserves_position_for_seekable_stream()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'sample content');
        $stream = new Stream(stream: $handle);
        $stream->seek(offset: 7);
        
        $body = new RequestBody(stream: $stream);
        $content = $body->content();
        
        $this->assertEquals(expected: 'sample content', actual: $content);
        $this->assertEquals(expected: 7, actual: $stream->tell());
    }

    public function test_parse_body_by_content_type_routes_json()
    {
        $router = new ParseBodyByContentType();
        $result = $router->execute(contentType: 'application/json', content: '{"foo": "bar"}');
        $this->assertEquals(expected: ['foo' => 'bar'], actual: $result);
    }
}
