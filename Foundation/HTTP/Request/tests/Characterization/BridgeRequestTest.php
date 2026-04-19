<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Characterization;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\AssembleIncomingRequest;
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
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class BridgeRequestTest extends TestCase
{
    private AssembleIncomingRequest $assembler;

    /**
     */
    public function test_constructor_sets_properties() : void
    {
        $request = $this->createServerRequest(method: 'POST', uri: 'https://example.com/api');

        $this->assertEquals(expected: 'POST', actual: $request->method);
        $this->assertEquals(expected: 'example.com', actual: $request->uri->getHost());
        $this->assertEquals(expected: '/api', actual: $request->requestTarget);
    }

    private function createServerRequest(string|null $method = null, string $uri = 'http://localhost/') : ServerRequest
    {
        $method ??= 'GET';

        return $this->assembler->fromSlices(
            queryParams: [],
            parsedBody : null,
            method     : $method
        )->withUri(uri: UriBuilder::createFromString(uri: $uri));
    }

    /**
     * @throws ReflectionException
     */
    public function test_with_uri_preserve_host_logic() : void
    {
        $serverRequest = $this->createServerRequest(uri: 'https://old.com/');
        $request       = (new ReflectionClass(objectOrClass: Request::class))->newInstanceWithoutConstructor();
        $property      = (new ReflectionClass(objectOrClass: Request::class))->getProperty(name: 'serverRequest');
        $property->setValue(objectOrValue: $request, value: $serverRequest);

        $newUri = UriBuilder::createFromString(uri: 'https://new.com/');

        $requestWithNewHost = $request->withUri(uri: $newUri, preserveHost: false);
        $this->assertEquals(expected: ['new.com'], actual: $requestWithNewHost->getHeader(name: 'Host'));

        $requestPreserved = $request->withUri(uri: $newUri, preserveHost: true);
        $this->assertEquals(expected: ['old.com'], actual: $requestPreserved->getHeader(name: 'Host'));
    }

    /**
     * @throws ReflectionException
     */
    public function test_get_request_target_behavior() : void
    {
        $serverRequest = $this->createServerRequest(uri: 'http://localhost/path?query=1');
        $request       = (new ReflectionClass(objectOrClass: Request::class))->newInstanceWithoutConstructor();
        $property      = (new ReflectionClass(objectOrClass: Request::class))->getProperty(name: 'serverRequest');
        $property->setValue(objectOrValue: $request, value: $serverRequest);

        $this->assertEquals(expected: '/path?query=1', actual: $request->getRequestTarget());

        $requestWithTarget = $request->withRequestTarget(requestTarget: '*');
        $this->assertEquals(expected: '*', actual: $requestWithTarget->getRequestTarget());
    }

    /**
     * @throws ReflectionException
     */
    public function test_header_casing_and_aggregation() : void
    {
        $serverRequest = $this->createServerRequest();
        $serverRequest = $serverRequest->withHeader(name: 'X-Test', value: 'val1');
        $serverRequest = $serverRequest->withAddedHeader(name: 'X-Test', value: 'val2');

        $request  = (new ReflectionClass(objectOrClass: Request::class))->newInstanceWithoutConstructor();
        $property = (new ReflectionClass(objectOrClass: Request::class))->getProperty(name: 'serverRequest');
        $property->setValue(objectOrValue: $request, value: $serverRequest);

        $this->assertTrue(condition: $request->hasHeader(name: 'X-Test'));
        $this->assertEquals(expected: ['val1', 'val2'], actual: $request->getHeader(name: 'X-Test'));
        $this->assertEquals(expected: 'val1,val2', actual: $request->getHeaderLine(name: 'X-Test'));
    }

    /**
     * @throws ReflectionException
     */
    public function test_without_header_removes_specific_header() : void
    {
        $serverRequest = $this->createServerRequest();
        $serverRequest = $serverRequest->withHeader(name: 'X-Test', value: 'value');
        $serverRequest = $serverRequest->withoutHeader(name: 'X-Test');

        $request  = (new ReflectionClass(objectOrClass: Request::class))->newInstanceWithoutConstructor();
        $property = (new ReflectionClass(objectOrClass: Request::class))->getProperty(name: 'serverRequest');
        $property->setValue(objectOrValue: $request, value: $serverRequest);

        $this->assertFalse(condition: $request->hasHeader(name: 'X-Test'));
    }

    protected function setUp() : void
    {
        $preparer = new PrepareRequest(
            bodyParser        : new ParseBodyByContentType(
                                    jsonParser: new ParseJsonBody,
                                    formParser: new ParseFormBody
                                ),
            protocolNormalizer: new NormalizeProtocolVersion,
            filesNormalizer   : new NormalizeUploadedFiles,
            trustedProxyPolicy: new TrustedProxyPolicy,
            forwardedParser   : new ParseForwardedAddresses,
            clientResolver    : new ResolveClientAddress(
                                    proxyPolicy    : new TrustedProxyPolicy,
                                    forwardedParser: new ParseForwardedAddresses
                                )
        );

        $this->assembler = new AssembleIncomingRequest(
            preparer : $preparer,
            sanitizer: new InputSanitizer,
            mapper   : new MapRequestedInputsToDto
        );
    }
}

