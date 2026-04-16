<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use ReflectionClass;

class BridgeRequestImmutabilityTest extends TestCase
{
    private function createRequest(): Request
    {
        $serverRequest = new ServerRequest(
            body: new RequestBody(stream: new Stream(stream: fopen('php://temp', 'r+'))),
            method: 'GET',
            uri: UriBuilder::createFromString(uri: 'http://localhost/')
        );
        
        // Use reflection to call the private constructor
        $reflection = new ReflectionClass(objectOrClass: Request::class);
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(accessible: true);
        $request = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke($request, $serverRequest);
        
        return $request;
    }

    public function test_withHeader_returns_new_bridge_instance()
    {
        $request = $this->createRequest();
        $newRequest = $request->withHeader(name: 'X-Test', value: 'Value');
        
        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertEquals(expected: ['Value'], actual: $newRequest->getHeader(name: 'X-Test'));
    }

    public function test_withMethod_returns_new_bridge_instance()
    {
        $request = $this->createRequest();
        $newRequest = $request->withMethod(method: 'POST');
        
        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertEquals(expected: 'POST', actual: $newRequest->getMethod());
    }

    public function test_withUri_returns_new_bridge_instance()
    {
        $request = $this->createRequest();
        $uri = UriBuilder::createFromString(uri: 'http://example.com/');
        $newRequest = $request->withUri(uri: $uri);
        
        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertEquals(expected: 'example.com', actual: $newRequest->getUri()->getHost());
    }

    public function test_withParsedBody_returns_new_bridge_instance()
    {
        $request = $this->createRequest();
        $body = ['foo' => 'bar'];
        $newRequest = $request->withParsedBody(data: $body);
        
        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertEquals(expected: $body, actual: $newRequest->getParsedBody());
    }

    public function test_legacy_bridge_preserves_original_instance_after_with_calls()
    {
        $request = $this->createRequest();
        $request->withHeader(name: 'X-Test', value: 'Value');
        $request->withMethod(method: 'POST');
        
        $this->assertEquals(expected: 'GET', actual: $request->getMethod());
        $this->assertFalse(condition: $request->hasHeader(name: 'X-Test'));
    }
}
