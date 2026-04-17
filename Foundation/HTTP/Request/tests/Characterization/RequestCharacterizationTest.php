<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Characterization;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Characterization tests for the old ServerRequest object to establish a baseline
 * of current behavior before refactoring.
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
     * @throws ReflectionException
     */
    public function test_getRequestTarget_returns_path_and_query() : void
    {
        $uri     = new UriBuilder(scheme: 'http://example.com/api/users?status=active');
        $request = new Request(uri: $uri);

        $this->assertSame(expected: '/api/users?status=active', actual: $request->getRequestTarget());
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
     * @throws ReflectionException
     */
    private function createBlankRequest(array $serverParams = []) : Request
    {
        return new Request(
            session      : null,
            serverParams : $serverParams,
            uri          : new UriBuilder(scheme: 'http://localhost'),
            body         : new Stream(stream: fopen('php://temp', 'r+')),
            queryParams  : [],
            parsedBody   : [],
            cookies      : [],
            uploadedFiles: []
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
        $newUri  = new UriBuilder(scheme: 'http://new-host.com/api');

        $newRequest = $request->withUri(uri: $newUri, preserveHost: false);

        $this->assertSame(expected: ['new-host.com'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_withUri_preserves_host_header_when_preserve_host_true() : void
    {
        $request = $this->createBlankRequest()->withHeader(name: 'Host', value: 'old-host.com');
        $newUri  = new UriBuilder(scheme: 'http://new-host.com/api');

        $newRequest = $request->withUri(uri: $newUri, preserveHost: true);

        $this->assertSame(expected: ['old-host.com'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_withQueryParams_replaces_query_params() : void
    {
        $request    = $this->createBlankRequest();
        $newRequest = $request->withQueryParams(query: ['search' => 'test']);

        $this->assertSame(expected: [], actual: $request->getQueryParams());
        $this->assertSame(expected: ['search' => 'test'], actual: $newRequest->getQueryParams());
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

        $this->assertSame(expected: $serverParams, actual: $request->getServerParams());
    }
}
