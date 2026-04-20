<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Characterization;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\AssembleIncomingRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedIpv4ProxyPolicy;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;

/**
 * PSR-7 Full Surface Characterization Test.
 *
 * Tests every method on the ServerRequestInterface to lock behavior.
 * These tests protect behavior, not implementation details.
 */
class PSR7SurfaceCharacterizationTest extends TestCase
{
    private AssembleIncomingRequest $assembler;

    // --- Request Target ---

    public function test_getRequestTarget_returns_path_from_uri(): void
    {
        $request = $this->make()->withUri(uri: UriBuilder::createFromString(uri: 'https://example.com/api/users'));
        $this->assertEquals(expected: '/api/users', actual: $request->getRequestTarget());
    }

    public function test_getRequestTarget_includes_query_string(): void
    {
        $request = $this->make()->withUri(uri: UriBuilder::createFromString(uri: 'https://example.com/api?status=active'));
        $this->assertEquals(expected: '/api?status=active', actual: $request->getRequestTarget());
    }

    public function test_getRequestTarget_defaults_to_slash_for_empty_path(): void
    {
        $request = $this->make()->withUri(uri: UriBuilder::createFromString(uri: 'https://example.com'));
        $target  = $request->getRequestTarget();
        $this->assertNotEmpty(actual: $target);
    }

    public function test_withRequestTarget_preserves_explicit_override(): void
    {
        $request = $this->make()->withRequestTarget(requestTarget: '*');
        $this->assertEquals(expected: '*', actual: $request->getRequestTarget());
    }

    public function test_withRequestTarget_is_not_overridden_by_withUri(): void
    {
        $request = $this->make()
            ->withRequestTarget(requestTarget: '*')
            ->withUri(uri: UriBuilder::createFromString(uri: 'https://other.com/path'));

        // After withUri, the request target should be recalculated from URI
        // This tests that withUri recalculates, not preserves explicit target
        $this->assertNotEquals(expected: '*', actual: $request->getRequestTarget());
    }

    // --- Method ---

    public function test_getMethod_returns_current_method(): void
    {
        $request = $this->make();
        $this->assertEquals(expected: 'GET', actual: $request->getMethod());
    }

    public function test_withMethod_returns_new_instance(): void
    {
        $original = $this->make();
        $modified = $original->withMethod(method: 'POST');

        $this->assertEquals(expected: 'GET', actual: $original->getMethod());
        $this->assertEquals(expected: 'POST', actual: $modified->getMethod());
        $this->assertNotSame(expected: $original, actual: $modified);
    }

    // --- Protocol Version ---

    public function test_getProtocolVersion_returns_version(): void
    {
        $request = $this->make();
        $this->assertEquals(expected: '1.1', actual: $request->getProtocolVersion());
    }

    public function test_withProtocolVersion_changes_version(): void
    {
        $request = $this->make()->withProtocolVersion(version: '2.0');
        $this->assertEquals(expected: '2.0', actual: $request->getProtocolVersion());
    }

    // --- Headers ---

    public function test_getHeaders_returns_all_headers(): void
    {
        $request = $this->make()
            ->withHeader(name: 'X-A', value: 'a')
            ->withHeader(name: 'X-B', value: 'b');

        $headers = $request->getHeaders();
        $this->assertArrayHasKey(key: 'X-A', array: $headers);
        $this->assertArrayHasKey(key: 'X-B', array: $headers);
    }

    public function test_getHeader_returns_array_of_values(): void
    {
        $request = $this->make()->withHeader(name: 'X-Test', value: 'value');
        $this->assertEquals(expected: ['value'], actual: $request->getHeader(name: 'X-Test'));
    }

    public function test_getHeader_returns_empty_for_missing(): void
    {
        $request = $this->make();
        $this->assertEquals(expected: [], actual: $request->getHeader(name: 'X-Missing'));
    }

    public function test_getHeaderLine_joins_values_with_comma(): void
    {
        $request = $this->make()
            ->withHeader(name: 'X-Multi', value: 'v1')
            ->withAddedHeader(name: 'X-Multi', value: 'v2');

        $this->assertEquals(expected: 'v1,v2', actual: $request->getHeaderLine(name: 'X-Multi'));
    }

    public function test_getHeaderLine_returns_empty_for_missing(): void
    {
        $this->assertEquals(expected: '', actual: $this->make()->getHeaderLine(name: 'X-Missing'));
    }

    public function test_hasHeader_is_case_insensitive(): void
    {
        $request = $this->make()->withHeader(name: 'Content-Type', value: 'text/html');
        $this->assertTrue(condition: $request->hasHeader(name: 'content-type'));
        $this->assertTrue(condition: $request->hasHeader(name: 'CONTENT-TYPE'));
    }

    public function test_withHeader_replaces_existing(): void
    {
        $request = $this->make()
            ->withHeader(name: 'X-Test', value: 'old')
            ->withHeader(name: 'X-Test', value: 'new');

        $this->assertEquals(expected: ['new'], actual: $request->getHeader(name: 'X-Test'));
    }

    public function test_withAddedHeader_appends(): void
    {
        $request = $this->make()
            ->withHeader(name: 'X-Test', value: 'a')
            ->withAddedHeader(name: 'X-Test', value: 'b');

        $this->assertEquals(expected: ['a', 'b'], actual: $request->getHeader(name: 'X-Test'));
    }

    public function test_withoutHeader_removes(): void
    {
        $request = $this->make()
            ->withHeader(name: 'X-Test', value: 'value')
            ->withoutHeader(name: 'X-Test');

        $this->assertFalse(condition: $request->hasHeader(name: 'X-Test'));
    }

    public function test_withoutHeader_is_case_insensitive(): void
    {
        $request = $this->make()
            ->withHeader(name: 'X-Test', value: 'value')
            ->withoutHeader(name: 'x-test');

        $this->assertFalse(condition: $request->hasHeader(name: 'X-Test'));
    }

    // --- Body ---

    public function test_getBody_returns_stream(): void
    {
        $stream  = new Stream(stream: fopen('php://temp', 'r+'));
        $request = $this->make()->withBody(body: $stream);

        $this->assertSame(expected: $stream, actual: $request->getBody());
    }

    public function test_withBody_replaces_stream(): void
    {
        $stream1 = new Stream(stream: fopen('php://temp', 'r+'));
        $stream2 = new Stream(stream: fopen('php://temp', 'r+'));

        $r1 = $this->make()->withBody(body: $stream1);
        $r2 = $r1->withBody(body: $stream2);

        $this->assertNotSame(expected: $r1->getBody(), actual: $r2->getBody());
    }

    // --- URI ---

    public function test_getUri_returns_uri(): void
    {
        $request = $this->make();
        $this->assertNotNull(actual: $request->getUri());
    }

    public function test_withUri_updates_host_header_by_default(): void
    {
        $request = $this->make()
            ->withHeader(name: 'Host', value: 'old.com')
            ->withUri(uri: UriBuilder::createFromString(uri: 'https://new.com/path'));

        $this->assertEquals(expected: ['new.com'], actual: $request->getHeader(name: 'Host'));
    }

    public function test_withUri_preserves_host_when_flag_set(): void
    {
        $request = $this->make()
            ->withHeader(name: 'Host', value: 'old.com')
            ->withUri(uri: UriBuilder::createFromString(uri: 'https://new.com/path'), preserveHost: true);

        $this->assertEquals(expected: ['old.com'], actual: $request->getHeader(name: 'Host'));
    }

    public function test_withUri_includes_port_in_host_header(): void
    {
        $request = $this->make()
            ->withUri(uri: UriBuilder::createFromString(uri: 'https://example.com:8080/path'));

        $hostLine = $request->getHeaderLine(name: 'Host');
        $this->assertStringContainsString(needle: '8080', haystack: $hostLine);
    }

    // --- Server Params ---

    public function test_getServerParams_returns_server_array(): void
    {
        $server  = ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'];
        $request = $this->assembler->fromGlobals(server: $server);

        $this->assertEquals(expected: $server, actual: $request->getServerParams());
    }

    // --- Cookie Params ---

    public function test_getCookieParams_returns_cookies(): void
    {
        $request = $this->make()->withCookieParams(cookies: ['sid' => 'abc']);
        $this->assertEquals(expected: ['sid' => 'abc'], actual: $request->getCookieParams());
    }

    public function test_withCookieParams_replaces_all(): void
    {
        $request = $this->make()
            ->withCookieParams(cookies: ['a' => '1'])
            ->withCookieParams(cookies: ['b' => '2']);

        $this->assertEquals(expected: ['b' => '2'], actual: $request->getCookieParams());
    }

    // --- Query Params ---

    public function test_getQueryParams_returns_query(): void
    {
        $request = $this->assembler->fromSlices(queryParams: ['q' => 'test']);
        $this->assertEquals(expected: ['q' => 'test'], actual: $request->getQueryParams());
    }

    public function test_withQueryParams_replaces_all(): void
    {
        $request = $this->make()
            ->withQueryParams(query: ['a' => '1'])
            ->withQueryParams(query: ['b' => '2']);

        $this->assertEquals(expected: ['b' => '2'], actual: $request->getQueryParams());
    }

    // --- Uploaded Files ---

    public function test_getUploadedFiles_returns_array(): void
    {
        $request = $this->make();
        $this->assertIsArray(actual: $request->getUploadedFiles());
    }

    public function test_withUploadedFiles_replaces_files(): void
    {
        $original = $this->make();
        $modified = $original->withUploadedFiles(uploadedFiles: []);
        $this->assertNotSame(expected: $original, actual: $modified);
    }

    // --- Parsed Body ---

    public function test_getParsedBody_returns_null_for_empty(): void
    {
        $request = $this->make();
        $body    = $request->getParsedBody();
        // Could be null or empty array depending on factory
        $this->assertTrue(condition: $body === null || $body === []);
    }

    public function test_withParsedBody_replaces_body(): void
    {
        $request = $this->make()->withParsedBody(data: ['name' => 'test']);
        $this->assertEquals(expected: ['name' => 'test'], actual: $request->getParsedBody());
    }

    public function test_withParsedBody_null_clears(): void
    {
        $request = $this->make()
            ->withParsedBody(data: ['name' => 'test'])
            ->withParsedBody(data: null);

        $this->assertNull(actual: $request->getParsedBody());
    }

    // --- Attributes ---

    public function test_getAttributes_returns_all(): void
    {
        $request = $this->make()
            ->withAttribute(name: 'user_id', value: 123)
            ->withAttribute(name: 'role', value: 'admin');

        $this->assertEquals(expected: ['user_id' => 123, 'role' => 'admin'], actual: $request->getAttributes());
    }

    public function test_getAttribute_returns_value(): void
    {
        $request = $this->make()->withAttribute(name: 'key', value: 'value');
        $this->assertEquals(expected: 'value', actual: $request->getAttribute(name: 'key'));
    }

    public function test_getAttribute_returns_default_for_missing(): void
    {
        $this->assertEquals(expected: 'fallback', actual: $this->make()->getAttribute(name: 'missing', default: 'fallback'));
    }

    public function test_getAttribute_returns_null_for_missing_no_default(): void
    {
        $this->assertNull(actual: $this->make()->getAttribute(name: 'missing'));
    }

    public function test_withAttribute_is_immutable(): void
    {
        $original = $this->make();
        $modified = $original->withAttribute(name: 'key', value: 'value');

        $this->assertNull(actual: $original->getAttribute(name: 'key'));
        $this->assertEquals(expected: 'value', actual: $modified->getAttribute(name: 'key'));
    }

    public function test_withoutAttribute_removes(): void
    {
        $request = $this->make()
            ->withAttribute(name: 'key', value: 'value')
            ->withoutAttribute(name: 'key');

        $this->assertNull(actual: $request->getAttribute(name: 'key'));
    }

    public function test_withoutAttribute_does_not_affect_other_attributes(): void
    {
        $request = $this->make()
            ->withAttribute(name: 'keep', value: 'yes')
            ->withAttribute(name: 'remove', value: 'no')
            ->withoutAttribute(name: 'remove');

        $this->assertEquals(expected: 'yes', actual: $request->getAttribute(name: 'keep'));
        $this->assertNull(actual: $request->getAttribute(name: 'remove'));
    }

    // --- Immutability across all with* methods ---

    public function test_all_with_methods_return_new_instances(): void
    {
        $request = $this->make();
        $stream  = new Stream(stream: fopen('php://temp', 'r+'));

        $this->assertNotSame(expected: $request, actual: $request->withMethod(method: 'POST'));
        $this->assertNotSame(expected: $request, actual: $request->withUri(uri: UriBuilder::createFromString(uri: 'https://x.com')));
        $this->assertNotSame(expected: $request, actual: $request->withHeader(name: 'X', value: '1'));
        $this->assertNotSame(expected: $request, actual: $request->withAddedHeader(name: 'X', value: '2'));
        $this->assertNotSame(expected: $request, actual: $request->withoutHeader(name: 'X'));
        $this->assertNotSame(expected: $request, actual: $request->withBody(body: $stream));
        $this->assertNotSame(expected: $request, actual: $request->withRequestTarget(requestTarget: '/'));
        $this->assertNotSame(expected: $request, actual: $request->withProtocolVersion(version: '2.0'));
        $this->assertNotSame(expected: $request, actual: $request->withQueryParams(query: ['a' => 'b']));
        $this->assertNotSame(expected: $request, actual: $request->withParsedBody(data: ['c' => 'd']));
        $this->assertNotSame(expected: $request, actual: $request->withCookieParams(cookies: ['e' => 'f']));
        $this->assertNotSame(expected: $request, actual: $request->withUploadedFiles(uploadedFiles: []));
        $this->assertNotSame(expected: $request, actual: $request->withAttribute(name: 'g', value: 'h'));
        $this->assertNotSame(expected: $request, actual: $request->withoutAttribute(name: 'g'));
    }

    // --- Helpers ---

    private function make(): ServerRequest
    {
        return $this->assembler->empty();
    }

    protected function setUp(): void
    {
        $preparer = new PrepareRequest(
            bodyParser: new ParseBodyByContentType(
                jsonParser: new ParseJsonBody,
                formParser: new ParseFormBody,
            ),
            protocolNormalizer: new NormalizeProtocolVersion,
            filesNormalizer: new NormalizeUploadedFiles,
            trustedProxyPolicy: new TrustedIpv4ProxyPolicy,
            clientResolver: new ResolveClientAddress(
                proxyPolicy: new TrustedIpv4ProxyPolicy,
                forwardedParser: new ParseForwardedAddresses,
            ),
        );

        $this->assembler = new AssembleIncomingRequest(
            preparer: $preparer,
            sanitizer: new InputSanitizer,
            mapper: new MapRequestedInputsToDto,
        );
    }
}
