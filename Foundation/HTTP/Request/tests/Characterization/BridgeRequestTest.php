<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Characterization;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BridgeRequestTest extends TestCase
{
    private function createServerRequest(string|null $method = null, string $uri = 'http://localhost/') : ServerRequest
    {
        $method ??= 'GET';

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
        $request = (new ReflectionClass(objectOrClass: Request::class))->newInstanceWithoutConstructor();
        $property = (new ReflectionClass(objectOrClass: Request::class))->getProperty(name: 'serverRequest');
        $property->setValue(objectOrValue: $request, value: $serverRequest);

        $this->assertEquals(expected: 'POST', actual: $request->getMethod());
        $this->assertEquals(expected: 'example.com', actual: $request->getUri()->getHost());
        $this->assertEquals(expected: '/api', actual: $request->getRequestTarget());
    }

    public function test_with_uri_preserve_host_logic() : void
    {
        $serverRequest = $this->createServerRequest(uri: 'http://old.com/');
        $request = (new ReflectionClass(objectOrClass: Request::class))->newInstanceWithoutConstructor();
        $property = (new ReflectionClass(objectOrClass: Request::class))->getProperty(name: 'serverRequest');
        $property->setValue(objectOrValue: $request, value: $serverRequest);

        $newUri = UriBuilder::createFromString(uri: 'http://new.com/');
        
        // Preserve host = false (should change Host header)
        $requestWithNewHost = $request->withUri(uri: $newUri, preserveHost: false);
        $this->assertEquals(expected: ['new.com'], actual: $requestWithNewHost->getHeader(name: 'Host'));

        // Preserve host = true
        $requestPreserved = $request->withUri(uri: $newUri, preserveHost: true);
        $this->assertEquals(expected: ['old.com'], actual: $requestPreserved->getHeader(name: 'Host'));
    }

    public function test_get_request_target_behavior() : void
    {
        $serverRequest = $this->createServerRequest(uri: 'http://localhost/path?query=1');
        $request = (new ReflectionClass(objectOrClass: Request::class))->newInstanceWithoutConstructor();
        $property = (new ReflectionClass(objectOrClass: Request::class))->getProperty(name: 'serverRequest');
        $property->setValue(objectOrValue: $request, value: $serverRequest);

        $this->assertEquals(expected: '/path?query=1', actual: $request->getRequestTarget());

        $requestWithTarget = $request->withRequestTarget(requestTarget: '*');
        $this->assertEquals(expected: '*', actual: $requestWithTarget->getRequestTarget());
    }

    public function test_header_casing_and_aggregation() : void
    {
        $serverRequest = $this->createServerRequest();
        $serverRequest = $serverRequest->withHeader(name: 'X-Test', value: 'val1');
        $serverRequest = $serverRequest->withAddedHeader(name: 'x-test', value: 'val2');

        $request = (new ReflectionClass(objectOrClass: Request::class))->newInstanceWithoutConstructor();
        $property = (new ReflectionClass(objectOrClass: Request::class))->getProperty(name: 'serverRequest');
        $property->setValue(objectOrValue: $request, value: $serverRequest);

        $this->assertTrue(condition: $request->hasHeader(name: 'X-TEST'));
        $this->assertEquals(expected: ['val1', 'val2'], actual: $request->getHeader(name: 'x-test'));
        $this->assertEquals(expected: 'val1, val2', actual: $request->getHeaderLine(name: 'X-Test'));

        $requestWithout = $request->withoutHeader(name: 'x-test');
        $this->assertFalse(condition: $requestWithout->hasHeader(name: 'X-Test'));
    }
}
