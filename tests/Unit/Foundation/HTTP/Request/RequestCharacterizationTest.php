<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Foundation\HTTP\Request;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Response\Capabilities\Streams\ResponseStreamFactory;
use Avax\HTTP\URI\UriBuilder;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

/**
 * Characterization tests for the existing ServerRequest class.
 * These tests define the current behavior of the ServerRequest object prior to the refactor,
 * acting as a safety net to ensure we do not break existing PSR-7 expected behavior.
 *
 * Target capabilities to map:
 * - getRequestTarget()
 * - withHeader(), withAddedHeader(), withoutHeader()
 * - withUri() preserveHost false/true
 * - withUploadedFiles()
 * - getParsedBody() / withParsedBody()
 * - withQueryParams()
 * - withCookieParams()
 * - withAttribute() / withoutAttribute()
 * - getServerParams()
 */
class RequestCharacterizationTest extends TestCase
{
    /**
     */
    public function test_getRequestTarget_returns_path_and_query() : void
    {
        $uri     = UriBuilder::createFromString(uri: 'https://example.com/api/users?status=active');
        $request = new ServerRequest(
            body           : new RequestBody(stream: new ResponseStreamFactory()->createEmptyStream()),
            method: 'GET',
            uri: $uri,
            requestHeaders: null,
            serverParams: [],
            requestTarget: null,
            cookies: null,
            queryParams: [],
            uploadedFiles: null,
            parsedBody: null,
            attributes: null,
            session: null,
            protocolVersion: '1.1'
        );

        $this->assertSame(expected: '/api/users?status=active', actual: $request->requestTarget);
    }

    public function test_withHeader_replaces_existing_header_preserves_immutability() : void
    {
        $request    = $this->createBlankRequest();
        $newRequest = $request->withHeader(name: 'X-Custom', value: 'Alpha');

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertFalse(condition: $request->hasHeader(name: 'X-Custom'));
        $this->assertTrue(condition: $newRequest->hasHeader(name: 'X-Custom'));
        $this->assertSame(expected: ['Alpha'], actual: $newRequest->getHeader(name: 'X-Custom'));

        // Replace
        $replacedRequest = $newRequest->withHeader(name: 'X-Custom', value: 'Beta');
        $this->assertSame(expected: ['Beta'], actual: $replacedRequest->getHeader(name: 'X-Custom'));
    }

    /**
     * @param array $serverParams
     *
     * @return ServerRequest
     */
    private function createBlankRequest(array $serverParams = []) : ServerRequest
    {
        return new ServerRequest(
            body           : new RequestBody(stream: new ResponseStreamFactory()->createEmptyStream()),
            method: 'GET',
            uri: UriBuilder::createFromString(uri: 'http://localhost'),
            requestHeaders: null,
            serverParams: $serverParams,
            requestTarget: null,
            cookies: null,
            queryParams: [],
            uploadedFiles: null,
            parsedBody: null,
            attributes: null,
            session: null,
            protocolVersion: '1.1'
        );
    }

    public function test_withAddedHeader_appends_new_value() : void
    {
        $request    = $this->createBlankRequest()->withHeader(name: 'X-Custom', value: 'Alpha');
        $newRequest = $request->withAddedHeader(name: 'X-Custom', value: 'Beta');

        $this->assertSame(expected: ['Alpha', 'Beta'], actual: $newRequest->getHeader(name: 'X-Custom'));
    }

    public function test_withoutHeader_removes_header() : void
    {
        $request    = $this->createBlankRequest()->withHeader(name: 'X-Custom', value: 'Alpha');
        $newRequest = $request->withoutHeader(name: 'X-Custom');

        $this->assertFalse(condition: $newRequest->hasHeader(name: 'X-Custom'));
    }

    public function test_withUri_updates_host_header_when_preserve_host_false() : void
    {
        $request = $this->createBlankRequest()->withHeader(name: 'Host', value: 'old-host.com');
        $newUri  = UriBuilder::createFromString(uri: 'https://new-host.com/api');

        $newRequest = $request->withUri(uri: $newUri, preserveHost: false);

        $this->assertSame(expected: ['new-host.com'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_withUri_preserves_host_header_when_preserve_host_true() : void
    {
        $request = $this->createBlankRequest()->withHeader(name: 'Host', value: 'old-host.com');
        $newUri  = UriBuilder::createFromString(uri: 'https://new-host.com/api');

        $newRequest = $request->withUri(uri: $newUri, preserveHost: true);

        $this->assertSame(expected: ['old-host.com'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_withQueryParams_replaces_query_params() : void
    {
        $request    = $this->createBlankRequest();
        $newRequest = $request->withQueryParams(query: ['search' => 'test']);

        $this->assertSame(expected: [], actual: $request->queryParams);
        $this->assertSame(expected: ['search' => 'test'], actual: $newRequest->queryParams);
    }

    public function test_withCookieParams_replaces_cookie_params() : void
    {
        $request    = $this->createBlankRequest();
        $newRequest = $request->withCookieParams(cookies: ['session_id' => '12345']);

        $this->assertSame(expected: [], actual: $request->getCookieParams());
        $this->assertSame(expected: ['session_id' => '12345'], actual: $newRequest->getCookieParams());
    }

    public function test_withParsedBody_replaces_parsed_body() : void
    {
        $request    = $this->createBlankRequest();
        $newRequest = $request->withParsedBody(data: ['name' => 'John']);

        $this->assertSame(expected: [], actual: $request->getParsedBody());
        $this->assertSame(expected: ['name' => 'John'], actual: $newRequest->getParsedBody());
    }

    public function test_withAttribute_and_withoutAttribute() : void
    {
        $request    = $this->createBlankRequest();
        $newRequest = $request->withAttribute(name: 'userId', value: 42);

        $this->assertNull(actual: $request->getAttribute(name: 'userId'));
        $this->assertSame(expected: 42, actual: $newRequest->getAttribute(name: 'userId'));

        $removedRequest = $newRequest->withoutAttribute(name: 'userId');
        $this->assertNull(actual: $removedRequest->getAttribute(name: 'userId'));
    }

    public function test_getServerParams_returns_raw_server_array() : void
    {
        $serverParams = ['REQUEST_METHOD' => 'POST', 'HTTP_HOST' => 'example.com'];
        $request      = $this->createBlankRequest(serverParams: $serverParams);

        $this->assertSame(expected: $serverParams, actual: $request->serverParams);
    }
}
