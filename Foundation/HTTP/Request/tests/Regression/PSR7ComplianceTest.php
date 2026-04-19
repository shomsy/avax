<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Regression;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\AssembleIncomingRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedIpv4ProxyPolicy;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * PSR7ComplianceTest - Ensuring the Request component honors PSR-7 contracts.
 */
class PSR7ComplianceTest extends TestCase
{
    private AssembleIncomingRequest $assembler;

    public function test_psr7_interface_adherence() : void
    {
        $request = $this->assembler->empty();
        
        $this->assertInstanceOf(expected: ServerRequestInterface::class, actual: $request);
    }

    public function test_immutability_on_all_with_methods() : void
    {
        $request = $this->assembler->empty();

        $this->assertNotSame(expected: $request, actual: $request->withMethod(method: 'POST'));
        $this->assertNotSame(expected: $request, actual: $request->withUri(uri: UriBuilder::createFromString(uri: 'https://avax.dev')));
        $this->assertNotSame(expected: $request, actual: $request->withHeader(name: 'X-Test', value: '1'));
        $this->assertNotSame(expected: $request, actual: $request->withAddedHeader(name: 'X-Test', value: '2'));
        $this->assertNotSame(expected: $request, actual: $request->withoutHeader(name: 'X-Test'));
        $this->assertNotSame(expected: $request, actual: $request->withQueryParams(query: ['a' => 'b']));
        $this->assertNotSame(expected: $request, actual: $request->withParsedBody(data: ['c' => 'd']));
        $this->assertNotSame(expected: $request, actual: $request->withCookieParams(cookies: ['e' => 'f']));
        $this->assertNotSame(expected: $request, actual: $request->withAttribute(name: 'g', value: 'h'));
        $this->assertNotSame(expected: $request, actual: $request->withoutAttribute(name: 'g'));
        $this->assertNotSame(expected: $request, actual: $request->withUploadedFiles(uploadedFiles: []));
    }

    public function test_header_behavior_is_correct() : void
    {
        $request = $this->assembler->empty()
            ->withHeader(name: 'X-Multi', value: 'v1')
            ->withAddedHeader(name: 'X-Multi', value: 'v2');

        $this->assertEquals(expected: ['v1', 'v2'], actual: $request->getHeader(name: 'X-Multi'));
        $this->assertEquals(expected: 'v1,v2', actual: $request->getHeaderLine(name: 'X-Multi'));
        $this->assertTrue(condition: $request->hasHeader(name: 'x-multi')); // Case insensitive
        
        $request = $request->withoutHeader(name: 'X-MULTI');
        $this->assertFalse(condition: $request->hasHeader(name: 'X-Multi'));
    }

    public function test_server_params_preservation() : void
    {
        $server  = ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'];
        $request = $this->assembler->fromGlobals(server: $server);
        
        $this->assertEquals(expected: $server, actual: $request->getServerParams());
    }

    public function test_with_uri_preserve_host_logic() : void
    {
        $request = $this->assembler->fromSlices()
            ->withHeader(name: 'Host', value: 'old.com');
        
        $newUri = UriBuilder::createFromString(uri: 'https://new.com/path');
        
        // preserveHost = false
        $r1 = $request->withUri(uri: $newUri);
        $this->assertEquals(expected: ['new.com'], actual: $r1->getHeader(name: 'Host'));
        
        // preserveHost = true
        $r2 = $request->withUri(uri: $newUri, preserveHost: true);
        $this->assertEquals(expected: ['old.com'], actual: $r2->getHeader(name: 'Host'));
    }

    public function test_attribute_management() : void
    {
        $request = $this->assembler->empty()
            ->withAttribute(name: 'user_id', value: 123)
            ->withAttribute(name: 'role', value: 'admin');
            
        $this->assertEquals(expected: 123, actual: $request->getAttribute(name: 'user_id'));
        $this->assertEquals(expected: ['user_id' => 123, 'role' => 'admin'], actual: $request->getAttributes());
        
        $request = $request->withoutAttribute(name: 'role');
        $this->assertNull(actual: $request->getAttribute(name: 'role'));
        $this->assertEquals(expected: 'default', actual: $request->getAttribute(name: 'role', default: 'default'));
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
            trustedProxyPolicy: new TrustedIpv4ProxyPolicy,
            clientResolver    : new ResolveClientAddress(
                proxyPolicy    : new TrustedIpv4ProxyPolicy,
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
