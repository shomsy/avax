<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\PublicEntryPointRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\URI\UriBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ServerRequestCorrectnessTest extends TestCase
{
    public function test_with_uploaded_files_rejects_invalid_tree()
    {
        $request = $this->createServerRequest();

        $this->expectException(InvalidArgumentException::class);
        $request->withUploadedFiles(uploadedFiles: ['invalid' => 'not-a-file-object']);
    }

    private function createServerRequest(): ServerRequest
    {
        return PublicEntryPointRequest::fromSlices(
            queryParams: [],
            parsedBody: null,
            method: 'GET'
        )->withUri(
            uri         : UriBuilder::createFromString(uri: 'http://localhost/'),
            preserveHost: false
        )->withHeader(name: 'Host', value: 'localhost');
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
        $newUri = UriBuilder::createFromString(uri: 'http://localhost/path-only');

        $newRequest = $request->withUri(uri: $newUri, preserveHost: false);

        $this->assertEquals(expected: ['localhost'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_request_target_falls_back_to_path_and_query()
    {
        $uri = UriBuilder::createFromString(uri: 'http://localhost/foo?bar=baz');
        $request = PublicEntryPointRequest::fromSlices(
            queryParams: [],
            parsedBody: null,
            method: 'GET'
        )->withUri(uri: $uri);

        $this->assertEquals(expected: '/foo?bar=baz', actual: $request->requestTarget);
    }
}
