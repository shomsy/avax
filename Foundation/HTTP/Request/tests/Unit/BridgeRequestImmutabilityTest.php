<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\RequestBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestInit;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedIpv4ProxyPolicy;
use Avax\HTTP\URI\UriBuilder;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class BridgeRequestImmutabilityTest extends TestCase
{
    private PrepareRequest          $preparer;
    private InputSanitizer          $sanitizer;
    private MapRequestedInputsToDto $mapper;

    public function test_with_header_returns_new_bridge_instance()
    {
        $request    = $this->createRequest();
        $newRequest = $request->withHeader(name: 'X-Test', value: 'Value');

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertEquals(expected: ['Value'], actual: $newRequest->getHeader(name: 'X-Test'));
    }

    /**
     * @throws ReflectionException
     */
    private function createRequest() : Request
    {
        $serverRequest = new ServerRequest(
            init     : new RequestInit(
                           body           : new RequestBody(stream: new Stream(stream: fopen('php://temp', 'r+'))),
                           method         : 'GET',
                           uri            : UriBuilder::createFromString(uri: 'http://localhost/'),
                           requestHeaders : null,
                           serverParams   : [],
                           requestTarget  : null,
                           cookies        : null,
                           queryParams    : [],
                           uploadedFiles  : null,
                           parsedBody     : null,
                           attributes     : null,
                           session        : null,
                           protocolVersion: '1.1'
                       ),
            preparer : $this->preparer,
            sanitizer: $this->sanitizer,
            mapper   : $this->mapper
        );

        // Use reflection to call the private constructor
        $reflection  = new ReflectionClass(objectOrClass: Request::class);
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(accessible: true);
        $request = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke($request, $serverRequest);

        return $request;
    }

    public function test_with_method_returns_new_bridge_instance()
    {
        $request    = $this->createRequest();
        $newRequest = $request->withMethod(method: 'POST');

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertEquals(expected: 'POST', actual: $newRequest->getMethod());
    }

    public function test_with_uri_returns_new_bridge_instance()
    {
        $request    = $this->createRequest();
        $uri        = UriBuilder::createFromString(uri: 'https://example.com/');
        $newRequest = $request->withUri(uri: $uri);

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertEquals(expected: 'example.com', actual: $newRequest->getUri()->getHost());
    }

    public function test_with_parsed_body_returns_new_bridge_instance()
    {
        $request    = $this->createRequest();
        $body       = ['foo' => 'bar'];
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

    protected function setUp() : void
    {
        $this->preparer  = new PrepareRequest(
            bodyParser        : new ParseBodyByContentType(
                                    jsonParser: new ParseJsonBody,
                                    formParser: new ParseFormBody
                                ),
            protocolNormalizer: new NormalizeProtocolVersion,
            filesNormalizer   : new NormalizeUploadedFiles,
            trustedProxyPolicy: new TrustedIpv4ProxyPolicy,
            forwardedParser   : new ParseForwardedAddresses,
            clientResolver    : new ResolveClientAddress(
                                    proxyPolicy    : new TrustedIpv4ProxyPolicy,
                                    forwardedParser: new ParseForwardedAddresses
                                )
        );
        $this->sanitizer = new InputSanitizer;
        $this->mapper    = new MapRequestedInputsToDto;
    }
}
