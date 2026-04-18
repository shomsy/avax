<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Response\Classes\Stream;
use PHPUnit\Framework\TestCase;

class RequestBodyTest extends TestCase
{
    public function test_content_reads_full_stream_regardless_of_cursor()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'hello world');
        
        $stream = new Stream(stream: $handle);
        $body = new RequestBody(stream: $stream);

        // Move cursor somewhere
        $stream->seek(6);

        // content() should still read everything
        $this->assertEquals('hello world', $body->content());
    }

    public function test_content_restores_cursor_on_seekable_stream()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'hello world');
        
        $stream = new Stream(stream: $handle);
        $body = new RequestBody(stream: $stream);

        $stream->seek(6);
        $body->content();

        // Cursor should be restored
        $this->assertEquals(6, $stream->tell());
    }

    public function test_repeated_reads_are_safe_on_seekable_stream()
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, 'safe read');
        
        $stream = new Stream(stream: $handle);
        $body = new RequestBody(stream: $stream);

        $read1 = $body->content();
        $read2 = $body->content();

        $this->assertEquals('safe read', $read1);
        $this->assertEquals('safe read', $read2);
    }

    public function test_stream_returns_raw_psr7_stream()
    {
        $handle = fopen('php://temp', 'r+');
        $stream = new Stream(stream: $handle);
        $body = new RequestBody(stream: $stream);

        $this->assertSame($stream, $body->stream());
    }
}
