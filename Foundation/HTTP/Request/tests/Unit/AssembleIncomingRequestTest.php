<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

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
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * AssembleIncomingRequestTest - Canonical entry flow verification.
 *
 * This replaces the removed PublicEntryPointTest.
 * AssembleIncomingRequest is the only public factory for ServerRequest.
 */
class AssembleIncomingRequestTest extends TestCase
{
    private AssembleIncomingRequest $assembler;

    public function test_from_globals_creates_valid_server_request(): void
    {
        $server = [
            'REQUEST_METHOD'  => 'POST',
            'REQUEST_URI'     => '/test',
            'HTTP_HOST'       => 'example.com',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];

        $request = $this->assembler->fromGlobals(server: $server);

        $this->assertInstanceOf(expected: ServerRequestInterface::class, actual: $request);
        $this->assertInstanceOf(expected: ServerRequest::class, actual: $request);
        $this->assertEquals(expected: 'POST', actual: $request->getMethod());
        $this->assertEquals(expected: '/test', actual: $request->getUri()->getPath());
        $this->assertEquals(expected: 'example.com', actual: $request->getUri()->getHost());
    }

    public function test_from_slices_creates_valid_server_request(): void
    {
        $request = $this->assembler->fromSlices(
            queryParams: ['foo' => 'bar'],
            parsedBody: ['baz' => 'qux'],
            method: 'PUT',
        );

        $this->assertEquals(expected: 'PUT', actual: $request->getMethod());
        $this->assertEquals(expected: ['foo' => 'bar'], actual: $request->getQueryParams());
        $this->assertEquals(expected: ['baz' => 'qux'], actual: $request->getParsedBody());
    }

    public function test_empty_creates_default_get_request(): void
    {
        $request = $this->assembler->empty();

        $this->assertEquals(expected: 'GET', actual: $request->getMethod());
        $this->assertEquals(expected: '1.1', actual: $request->getProtocolVersion());
        $this->assertInstanceOf(expected: ServerRequestInterface::class, actual: $request);
    }

    public function test_from_globals_preserves_server_params(): void
    {
        $server = [
            'REQUEST_METHOD' => 'GET',
            'HTTP_HOST'      => 'api.example.com',
            'REQUEST_URI'    => '/v1',
            'REMOTE_ADDR'    => '192.168.1.1',
        ];

        $request = $this->assembler->fromGlobals(server: $server);

        $this->assertEquals(expected: $server, actual: $request->getServerParams());
    }

    public function test_from_globals_with_query_and_cookies(): void
    {
        $server = ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'localhost'];

        $request = $this->assembler->fromGlobals(
            server: $server,
            query: ['q' => 'search'],
            cookie: ['session' => 'abc'],
        );

        $this->assertEquals(expected: ['q' => 'search'], actual: $request->getQueryParams());
        $this->assertEquals(expected: ['session' => 'abc'], actual: $request->getCookieParams());
    }

    public function test_from_slices_with_null_body(): void
    {
        $request = $this->assembler->fromSlices(
            queryParams: ['id' => '42'],
            parsedBody: null,
            method: 'GET',
        );

        $this->assertEquals(expected: ['id' => '42'], actual: $request->getQueryParams());
    }

    public function test_immutability_across_all_from_methods(): void
    {
        $r1 = $this->assembler->fromSlices(method: 'GET');
        $r2 = $this->assembler->fromSlices(method: 'POST');

        $this->assertNotSame(expected: $r1, actual: $r2);
        $this->assertEquals(expected: 'GET', actual: $r1->getMethod());
        $this->assertEquals(expected: 'POST', actual: $r2->getMethod());
    }

    public function test_inputs_capability_is_available(): void
    {
        $request = $this->assembler->fromSlices(
            queryParams: ['name' => 'test'],
            parsedBody: ['age' => '25'],
        );

        $inputs = $request->inputs();
        $this->assertEquals(expected: 'test', actual: $inputs->get(key: 'name'));
        $this->assertEquals(expected: '25', actual: $inputs->get(key: 'age'));
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
            forwardedParser: new ParseForwardedAddresses,
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
