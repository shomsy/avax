<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Characterization;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;

class BridgeRequestTest extends TestCase
{
    private function createServerRequest(string $method = 'GET', string $uri = 'http://localhost/') : ServerRequest
    {
        return new ServerRequest(
            body: new RequestBody(stream: new Stream(stream: fopen('php://temp', 'r+'))),
            method: $method,
            uri: UriBuilder::createFromString(uri: $uri)
        );
    }

    public function test_bridge_delegates_to_server_request() : void
    {
        $serverRequest = $this->createServerRequest(method: 'POST', uri: 'https://example.com/api');
        
        // Use reflection to set the private property since constructor is private and we want to inject a mock or specific instance
        $request = (new \ReflectionClass(Request::class))->newInstanceWithoutConstructor();
        $property = (new \ReflectionClass(Request::class))->getProperty('serverRequest');
        $property->setValue($request, $serverRequest);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('example.com', $request->getUri()->getHost());
        $this->assertEquals('/api', $request->getRequestTarget());
    }

    public function test_with_uri_preserve_host_logic() : void
    {
        $serverRequest = $this->createServerRequest(uri: 'http://old.com/');
        $request = (new \ReflectionClass(Request::class))->newInstanceWithoutConstructor();
        $property = (new \ReflectionClass(Request::class))->getProperty('serverRequest');
        $property->setValue($request, $serverRequest);

        $newUri = UriBuilder::createFromString(uri: 'http://new.com/');
        
        // Preserve host = false (should change Host header)
        $requestWithNewHost = $request->withUri($newUri, false);
        $this->assertEquals(['new.com'], $requestWithNewHost->getHeader('Host'));

        // Preserve host = true
        $requestPreserved = $request->withUri($newUri, true);
        $this->assertEquals(['old.com'], $requestPreserved->getHeader('Host'));
    }

    public function test_get_request_target_behavior() : void
    {
        $serverRequest = $this->createServerRequest(uri: 'http://localhost/path?query=1');
        $request = (new \ReflectionClass(Request::class))->newInstanceWithoutConstructor();
        $property = (new \ReflectionClass(Request::class))->getProperty('serverRequest');
        $property->setValue($request, $serverRequest);

        $this->assertEquals('/path?query=1', $request->getRequestTarget());

        $requestWithTarget = $request->withRequestTarget('*');
        $this->assertEquals('*', $requestWithTarget->getRequestTarget());
    }

    public function test_header_casing_and_aggregation() : void
    {
        $serverRequest = $this->createServerRequest();
        $serverRequest = $serverRequest->withHeader('X-Test', 'val1');
        $serverRequest = $serverRequest->withAddedHeader('x-test', 'val2');

        $request = (new \ReflectionClass(Request::class))->newInstanceWithoutConstructor();
        $property = (new \ReflectionClass(Request::class))->getProperty('serverRequest');
        $property->setValue($request, $serverRequest);

        $this->assertTrue($request->hasHeader('X-TEST'));
        $this->assertEquals(['val1', 'val2'], $request->getHeader('x-test'));
        $this->assertEquals('val1, val2', $request->getHeaderLine('X-Test'));

        $requestWithout = $request->withoutHeader('x-test');
        $this->assertFalse($requestWithout->hasHeader('X-Test'));
    }
}
