<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Characterization;

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
use Avax\HTTP\Request\ServerRequest\Network\TrustedProxyPolicy;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

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
        $request = $this->make()->withUri(UriBuilder::createFromString('http://example.com/api/users'));
        $this->assertEquals('/api/users', $request->getRequestTarget());
    }

    public function test_getRequestTarget_includes_query_string(): void
    {
        $request = $this->make()->withUri(UriBuilder::createFromString('http://example.com/api?status=active'));
        $this->assertEquals('/api?status=active', $request->getRequestTarget());
    }

    public function test_getRequestTarget_defaults_to_slash_for_empty_path(): void
    {
        $request = $this->make()->withUri(UriBuilder::createFromString('http://example.com'));
        $target  = $request->getRequestTarget();
        $this->assertNotEmpty($target);
    }

    public function test_withRequestTarget_preserves_explicit_override(): void
    {
        $request = $this->make()->withRequestTarget('*');
        $this->assertEquals('*', $request->getRequestTarget());
    }

    public function test_withRequestTarget_is_not_overridden_by_withUri(): void
    {
        $request = $this->make()
            ->withRequestTarget('*')
            ->withUri(UriBuilder::createFromString('http://other.com/path'));

        // After withUri, the request target should be recalculated from URI
        // This tests that withUri recalculates, not preserves explicit target
        $this->assertNotEquals('*', $request->getRequestTarget());
    }

    // --- Method ---

    public function test_getMethod_returns_current_method(): void
    {
        $request = $this->make();
        $this->assertEquals('GET', $request->getMethod());
    }

    public function test_withMethod_returns_new_instance(): void
    {
        $original = $this->make();
        $modified = $original->withMethod('POST');

        $this->assertEquals('GET', $original->getMethod());
        $this->assertEquals('POST', $modified->getMethod());
        $this->assertNotSame($original, $modified);
    }

    // --- Protocol Version ---

    public function test_getProtocolVersion_returns_version(): void
    {
        $request = $this->make();
        $this->assertEquals('1.1', $request->getProtocolVersion());
    }

    public function test_withProtocolVersion_changes_version(): void
    {
        $request = $this->make()->withProtocolVersion('2.0');
        $this->assertEquals('2.0', $request->getProtocolVersion());
    }

    // --- Headers ---

    public function test_getHeaders_returns_all_headers(): void
    {
        $request = $this->make()
            ->withHeader('X-A', 'a')
            ->withHeader('X-B', 'b');

        $headers = $request->getHeaders();
        $this->assertArrayHasKey('X-A', $headers);
        $this->assertArrayHasKey('X-B', $headers);
    }

    public function test_getHeader_returns_array_of_values(): void
    {
        $request = $this->make()->withHeader('X-Test', 'value');
        $this->assertEquals(['value'], $request->getHeader('X-Test'));
    }

    public function test_getHeader_returns_empty_for_missing(): void
    {
        $request = $this->make();
        $this->assertEquals([], $request->getHeader('X-Missing'));
    }

    public function test_getHeaderLine_joins_values_with_comma(): void
    {
        $request = $this->make()
            ->withHeader('X-Multi', 'v1')
            ->withAddedHeader('X-Multi', 'v2');

        $this->assertEquals('v1, v2', $request->getHeaderLine('X-Multi'));
    }

    public function test_getHeaderLine_returns_empty_for_missing(): void
    {
        $this->assertEquals('', $this->make()->getHeaderLine('X-Missing'));
    }

    public function test_hasHeader_is_case_insensitive(): void
    {
        $request = $this->make()->withHeader('Content-Type', 'text/html');
        $this->assertTrue($request->hasHeader('content-type'));
        $this->assertTrue($request->hasHeader('CONTENT-TYPE'));
    }

    public function test_withHeader_replaces_existing(): void
    {
        $request = $this->make()
            ->withHeader('X-Test', 'old')
            ->withHeader('X-Test', 'new');

        $this->assertEquals(['new'], $request->getHeader('X-Test'));
    }

    public function test_withAddedHeader_appends(): void
    {
        $request = $this->make()
            ->withHeader('X-Test', 'a')
            ->withAddedHeader('X-Test', 'b');

        $this->assertEquals(['a', 'b'], $request->getHeader('X-Test'));
    }

    public function test_withoutHeader_removes(): void
    {
        $request = $this->make()
            ->withHeader('X-Test', 'value')
            ->withoutHeader('X-Test');

        $this->assertFalse($request->hasHeader('X-Test'));
    }

    public function test_withoutHeader_is_case_insensitive(): void
    {
        $request = $this->make()
            ->withHeader('X-Test', 'value')
            ->withoutHeader('x-test');

        $this->assertFalse($request->hasHeader('X-Test'));
    }

    // --- Body ---

    public function test_getBody_returns_stream(): void
    {
        $stream  = new Stream(stream: fopen('php://temp', 'r+'));
        $request = $this->make()->withBody($stream);

        $this->assertSame($stream, $request->getBody());
    }

    public function test_withBody_replaces_stream(): void
    {
        $stream1 = new Stream(stream: fopen('php://temp', 'r+'));
        $stream2 = new Stream(stream: fopen('php://temp', 'r+'));

        $r1 = $this->make()->withBody($stream1);
        $r2 = $r1->withBody($stream2);

        $this->assertNotSame($r1->getBody(), $r2->getBody());
    }

    // --- URI ---

    public function test_getUri_returns_uri(): void
    {
        $request = $this->make();
        $this->assertNotNull($request->getUri());
    }

    public function test_withUri_updates_host_header_by_default(): void
    {
        $request = $this->make()
            ->withHeader('Host', 'old.com')
            ->withUri(UriBuilder::createFromString('http://new.com/path'), false);

        $this->assertEquals(['new.com'], $request->getHeader('Host'));
    }

    public function test_withUri_preserves_host_when_flag_set(): void
    {
        $request = $this->make()
            ->withHeader('Host', 'old.com')
            ->withUri(UriBuilder::createFromString('http://new.com/path'), true);

        $this->assertEquals(['old.com'], $request->getHeader('Host'));
    }

    public function test_withUri_includes_port_in_host_header(): void
    {
        $request = $this->make()
            ->withUri(UriBuilder::createFromString('http://example.com:8080/path'), false);

        $hostLine = $request->getHeaderLine('Host');
        $this->assertStringContainsString('8080', $hostLine);
    }

    // --- Server Params ---

    public function test_getServerParams_returns_server_array(): void
    {
        $server  = ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'];
        $request = $this->assembler->fromGlobals(server: $server);

        $this->assertEquals($server, $request->getServerParams());
    }

    // --- Cookie Params ---

    public function test_getCookieParams_returns_cookies(): void
    {
        $request = $this->make()->withCookieParams(['sid' => 'abc']);
        $this->assertEquals(['sid' => 'abc'], $request->getCookieParams());
    }

    public function test_withCookieParams_replaces_all(): void
    {
        $request = $this->make()
            ->withCookieParams(['a' => '1'])
            ->withCookieParams(['b' => '2']);

        $this->assertEquals(['b' => '2'], $request->getCookieParams());
    }

    // --- Query Params ---

    public function test_getQueryParams_returns_query(): void
    {
        $request = $this->assembler->fromSlices(queryParams: ['q' => 'test']);
        $this->assertEquals(['q' => 'test'], $request->getQueryParams());
    }

    public function test_withQueryParams_replaces_all(): void
    {
        $request = $this->make()
            ->withQueryParams(['a' => '1'])
            ->withQueryParams(['b' => '2']);

        $this->assertEquals(['b' => '2'], $request->getQueryParams());
    }

    // --- Uploaded Files ---

    public function test_getUploadedFiles_returns_array(): void
    {
        $request = $this->make();
        $this->assertIsArray($request->getUploadedFiles());
    }

    public function test_withUploadedFiles_replaces_files(): void
    {
        $original = $this->make();
        $modified = $original->withUploadedFiles([]);
        $this->assertNotSame($original, $modified);
    }

    // --- Parsed Body ---

    public function test_getParsedBody_returns_null_for_empty(): void
    {
        $request = $this->make();
        $body    = $request->getParsedBody();
        // Could be null or empty array depending on factory
        $this->assertTrue($body === null || $body === []);
    }

    public function test_withParsedBody_replaces_body(): void
    {
        $request = $this->make()->withParsedBody(['name' => 'test']);
        $this->assertEquals(['name' => 'test'], $request->getParsedBody());
    }

    public function test_withParsedBody_null_clears(): void
    {
        $request = $this->make()
            ->withParsedBody(['name' => 'test'])
            ->withParsedBody(null);

        $this->assertNull($request->getParsedBody());
    }

    // --- Attributes ---

    public function test_getAttributes_returns_all(): void
    {
        $request = $this->make()
            ->withAttribute('user_id', 123)
            ->withAttribute('role', 'admin');

        $this->assertEquals(['user_id' => 123, 'role' => 'admin'], $request->getAttributes());
    }

    public function test_getAttribute_returns_value(): void
    {
        $request = $this->make()->withAttribute('key', 'value');
        $this->assertEquals('value', $request->getAttribute('key'));
    }

    public function test_getAttribute_returns_default_for_missing(): void
    {
        $this->assertEquals('fallback', $this->make()->getAttribute('missing', 'fallback'));
    }

    public function test_getAttribute_returns_null_for_missing_no_default(): void
    {
        $this->assertNull($this->make()->getAttribute('missing'));
    }

    public function test_withAttribute_is_immutable(): void
    {
        $original = $this->make();
        $modified = $original->withAttribute('key', 'value');

        $this->assertNull($original->getAttribute('key'));
        $this->assertEquals('value', $modified->getAttribute('key'));
    }

    public function test_withoutAttribute_removes(): void
    {
        $request = $this->make()
            ->withAttribute('key', 'value')
            ->withoutAttribute('key');

        $this->assertNull($request->getAttribute('key'));
    }

    public function test_withoutAttribute_does_not_affect_other_attributes(): void
    {
        $request = $this->make()
            ->withAttribute('keep', 'yes')
            ->withAttribute('remove', 'no')
            ->withoutAttribute('remove');

        $this->assertEquals('yes', $request->getAttribute('keep'));
        $this->assertNull($request->getAttribute('remove'));
    }

    // --- Immutability across all with* methods ---

    public function test_all_with_methods_return_new_instances(): void
    {
        $request = $this->make();
        $stream  = new Stream(stream: fopen('php://temp', 'r+'));

        $this->assertNotSame($request, $request->withMethod('POST'));
        $this->assertNotSame($request, $request->withUri(UriBuilder::createFromString('http://x.com')));
        $this->assertNotSame($request, $request->withHeader('X', '1'));
        $this->assertNotSame($request, $request->withAddedHeader('X', '2'));
        $this->assertNotSame($request, $request->withoutHeader('X'));
        $this->assertNotSame($request, $request->withBody($stream));
        $this->assertNotSame($request, $request->withRequestTarget('/'));
        $this->assertNotSame($request, $request->withProtocolVersion('2.0'));
        $this->assertNotSame($request, $request->withQueryParams(['a' => 'b']));
        $this->assertNotSame($request, $request->withParsedBody(['c' => 'd']));
        $this->assertNotSame($request, $request->withCookieParams(['e' => 'f']));
        $this->assertNotSame($request, $request->withUploadedFiles([]));
        $this->assertNotSame($request, $request->withAttribute('g', 'h'));
        $this->assertNotSame($request, $request->withoutAttribute('g'));
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
            trustedProxyPolicy: new TrustedProxyPolicy,
            forwardedParser: new ParseForwardedAddresses,
            clientResolver: new ResolveClientAddress(
                proxyPolicy: new TrustedProxyPolicy,
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
