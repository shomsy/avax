<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\Configuration\CreateRequestFromIncomingHttp;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Response\Classes\Stream;

class CreateRequestTest extends TestCase
{
    public function test_execute_builds_server_request_from_explicit_inputs()
    {
        $creator = new CreateRequestFromIncomingHttp();
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'api.example.com',
            'REQUEST_URI' => '/v1/users',
            'SERVER_PROTOCOL' => 'HTTP/1.1'
        ];
        
        $request = $creator->execute(server: $server);
        
        $this->assertEquals(expected: 'POST', actual: $request->method);
        $this->assertEquals(expected: 'api.example.com', actual: $request->uri->getHost());
        $this->assertEquals(expected: '/v1/users', actual: $request->uri->getPath());
        $this->assertEquals(expected: '1.1', actual: $request->protocolVersion);
    }

    public function test_execute_uses_query_cookie_and_files_arguments()
    {
        $creator = new CreateRequestFromIncomingHttp();
        $server = ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'localhost'];
        $query = ['foo' => 'bar'];
        $cookie = ['session' => 'abc'];
        
        $request = $creator->execute(server: $server, query: $query, cookie: $cookie);
        
        $this->assertEquals(expected: $query, actual: $request->queryParams);
        $this->assertEquals(expected: $cookie, actual: $request->getCookieParams());
    }

    public function test_execute_uses_explicit_body_when_provided()
    {
        $creator = new CreateRequestFromIncomingHttp();
        $server = ['REQUEST_METHOD' => 'POST', 'HTTP_HOST' => 'localhost'];
        $stream = new Stream(stream: fopen('php://temp', 'r+'));
        $stream->write(string: 'hello world');
        
        $request = $creator->execute(server: $server, body: $stream);
        
        $this->assertEquals(expected: 'hello world', actual: (string) $request->getBody());
    }

    public function test_create_request_normalizes_http_1_1_to_1_1()
    {
        $creator = new CreateRequestFromIncomingHttp();
        $server = ['REQUEST_METHOD' => 'GET', 'SERVER_PROTOCOL' => 'HTTP/1.1'];
        
        $request = $creator->execute(server: $server);
        $this->assertEquals(expected: '1.1', actual: $request->protocolVersion);
    }

    public function test_create_request_defaults_protocol_version_to_1_1_when_missing()
    {
        $creator = new CreateRequestFromIncomingHttp();
        $server = ['REQUEST_METHOD' => 'GET'];
        
        $request = $creator->execute(server: $server);
        $this->assertEquals(expected: '1.1', actual: $request->protocolVersion);
    }

    public function test_resolve_body_accepts_request_body_instance()
    {
        $creator = new CreateRequestFromIncomingHttp();
        $server = ['REQUEST_METHOD' => 'POST'];
        $body = new RequestBody(stream: new Stream(stream: fopen('php://temp', 'r+')));
        
        $request = $creator->execute(server: $server, body: $body);
        $this->assertSame(expected: $body->stream(), actual: $request->getBody());
    }
}
