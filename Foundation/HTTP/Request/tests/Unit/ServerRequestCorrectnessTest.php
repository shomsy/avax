<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestHeaders\RequestHeaders;
use PHPUnit\Framework\TestCase;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use InvalidArgumentException;

class ServerRequestCorrectnessTest extends TestCase
{
    private function createServerRequest(): ServerRequest
    {
        return new ServerRequest(
            body: new RequestBody(stream: new Stream(stream: fopen('php://temp', 'r+'))),
            method: 'GET',
            uri: UriBuilder::createFromString(uri: 'http://localhost/'),
            headers: new RequestHeaders(headers: ['Host' => 'localhost'])
        );
    }

    public function test_with_uploaded_files_rejects_invalid_tree()
    {
        $request = $this->createServerRequest();
        
        $this->expectException(InvalidArgumentException::class);
        $request->withUploadedFiles(uploadedFiles: ['invalid' => 'not-a-file-object']);
    }

    public function test_with_uri_sets_host_when_preserve_host_is_false()
    {
        $request = $this->createServerRequest();
        $newUri = UriBuilder::createFromString(uri: 'http://example.com/foo');
        
        $newRequest = $request->withUri(uri: $newUri, preserveHost: false);
        
        $this->assertEquals(expected: ['example.com'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_with_uri_preserves_existing_host_when_requested()
    {
        $request = $this->createServerRequest();
        $newUri = UriBuilder::createFromString(uri: 'http://example.com/foo');
        
        $newRequest = $request->withUri(uri: $newUri, preserveHost: true);
        
        $this->assertEquals(expected: ['localhost'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_with_uri_handles_empty_host_safely()
    {
        $request = $this->createServerRequest();
        $newUri = UriBuilder::createFromString(uri: '/path-only'); // No host
        
        $newRequest = $request->withUri(uri: $newUri, preserveHost: false);
        
        // If URI has no host, Host header MUST NOT be updated.
        $this->assertEquals(expected: ['localhost'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_request_target_falls_back_to_path_and_query()
    {
        $uri = UriBuilder::createFromString(uri: 'http://localhost/foo?bar=baz');
        $request = new ServerRequest(
            body: new RequestBody(stream: new Stream(stream: fopen('php://temp', 'r+'))),
            uri: $uri
        );
        
        $this->assertEquals(expected: '/foo?bar=baz', actual: $request->requestTarget);
    }
}
