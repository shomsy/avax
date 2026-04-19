<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Response\Classes\Stream;
use PHPUnit\Framework\TestCase;

class BodyParsingTest extends TestCase
{
    public function test_parse_json_body_returns_array_for_json_object()
    {
        $parser = new ParseJsonBody;
        $this->assertEquals(expected: ['foo' => 'bar'], actual: $parser->execute(content: '{"foo": "bar"}'));
    }

    public function test_parse_json_body_returns_array_for_json_array()
    {
        $parser = new ParseJsonBody;
        $this->assertEquals(expected: ['a', 'b'], actual: $parser->execute(content: '["a", "b"]'));
    }

    public function test_parse_json_body_returns_null_for_empty_content()
    {
        $parser = new ParseJsonBody;
        $this->assertNull(actual: $parser->execute(content: ''));
        $this->assertNull(actual: $parser->execute(content: '   '));
    }

    public function test_parse_json_body_returns_null_for_invalid_json()
    {
        $parser = new ParseJsonBody;
        $this->assertNull(actual: $parser->execute(content: 'invalid-json'));
    }

    public function test_parse_form_body_parses_urlencoded_content()
    {
        $parser = new ParseFormBody;
        $this->assertEquals(expected: ['foo' => 'bar', 'baz' => '1'], actual: $parser->execute(content: 'foo=bar&baz=1'));
    }

    public function test_content_preserves_position_for_seekable_stream()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'sample content');
        $stream = new Stream(stream: $handle);
        $stream->seek(offset: 7);

        $body    = new RequestBody(stream: $stream);
        $content = $body->content();

        $this->assertEquals(expected: 'sample content', actual: $content);
        $this->assertEquals(expected: 7, actual: $stream->tell());
    }

    public function test_parse_body_by_content_type_routes_json()
    {
        $router = new ParseBodyByContentType(
            jsonParser: new ParseJsonBody,
            formParser: new ParseFormBody
        );
        $result = $router->execute(contentType: 'application/json', content: '{"foo": "bar"}');
        $this->assertEquals(expected: ['foo' => 'bar'], actual: $result);
    }

    public function test_content_reads_nonseekable_stream_directly()
    {
        $stream = new Stream(stream: fopen('php://temp', 'r+'));
        $stream->write(string: 'nonseekable content');
        $stream->rewind();

        $body    = new RequestBody(stream: $stream);
        $content = $body->content();

        $this->assertEquals(expected: 'nonseekable content', actual: $content);
    }

    public function test_content_handles_repeated_reads()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'repeatable content');
        $stream = new Stream(stream: $handle);

        $body = new RequestBody(stream: $stream);

        $firstRead  = $body->content();
        $secondRead = $body->content();

        $this->assertEquals(expected: 'repeatable content', actual: $firstRead);
        $this->assertEquals(expected: 'repeatable content', actual: $secondRead);
    }

    public function test_content_restores_cursor_position_after_read()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'cursor test content');
        $stream = new Stream(stream: $handle);
        $stream->seek(offset: 7);

        $body    = new RequestBody(stream: $stream);
        $content = $body->content();

        $this->assertEquals(expected: 'cursor test content', actual: $content);
        $this->assertEquals(expected: 7, actual: $stream->tell());
    }

    public function test_content_returns_empty_for_empty_stream()
    {
        $handle = fopen('php://temp', 'r+');
        $stream = new Stream(stream: $handle);

        $body    = new RequestBody(stream: $stream);
        $content = $body->content();

        $this->assertEquals(expected: '', actual: $content);
    }

    public function test_parse_body_returns_empty_for_get_method()
    {
        $parser = new ParseJsonBody;
        $result = $parser->execute(content: '{"foo": "bar"}');
        $this->assertNull(actual: $result);
    }

    public function test_parse_body_returns_empty_for_unsupported_content_type()
    {
        $router = new ParseBodyByContentType(
            jsonParser: new ParseJsonBody,
            formParser: new ParseFormBody
        );
        $result = $router->execute(
            contentType: 'application/xml',
            content    : '<foo>bar</foo>'
        );
        $this->assertNull(actual: $result);
    }

    public function test_parse_body_handles_charset_in_content_type()
    {
        $router = new ParseBodyByContentType(
            jsonParser: new ParseJsonBody,
            formParser: new ParseFormBody
        );
        $result = $router->execute(
            contentType: 'application/json; charset=utf-8',
            content    : '{"foo": "bar"}'
        );
        $this->assertEquals(expected: ['foo' => 'bar'], actual: $result);
    }

    public function test_parse_body_returns_empty_for_invalid_json()
    {
        $parser = new ParseJsonBody;
        $this->assertNull(actual: $parser->execute(content: '{invalid}'));
    }
}
