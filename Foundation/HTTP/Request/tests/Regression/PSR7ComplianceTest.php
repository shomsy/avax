<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Regression;

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
use Avax\HTTP\Request\ServerRequest\Network\TrustedProxyPolicy;
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
        
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
    }

    public function test_immutability_on_all_with_methods() : void
    {
        $request = $this->assembler->empty();

        $this->assertNotSame($request, $request->withMethod('POST'));
        $this->assertNotSame($request, $request->withUri(UriBuilder::createFromString('https://avax.dev')));
        $this->assertNotSame($request, $request->withHeader('X-Test', '1'));
        $this->assertNotSame($request, $request->withAddedHeader('X-Test', '2'));
        $this->assertNotSame($request, $request->withoutHeader('X-Test'));
        $this->assertNotSame($request, $request->withQueryParams(['a' => 'b']));
        $this->assertNotSame($request, $request->withParsedBody(['c' => 'd']));
        $this->assertNotSame($request, $request->withCookieParams(['e' => 'f']));
        $this->assertNotSame($request, $request->withAttribute('g', 'h'));
        $this->assertNotSame($request, $request->withoutAttribute('g'));
        $this->assertNotSame($request, $request->withUploadedFiles([]));
    }

    public function test_header_behavior_is_correct() : void
    {
        $request = $this->assembler->empty()
            ->withHeader('X-Multi', 'v1')
            ->withAddedHeader('X-Multi', 'v2');

        $this->assertEquals(['v1', 'v2'], $request->getHeader('X-Multi'));
        $this->assertEquals('v1,v2', $request->getHeaderLine('X-Multi'));
        $this->assertTrue($request->hasHeader('x-multi')); // Case insensitive
        
        $request = $request->withoutHeader('X-MULTI');
        $this->assertFalse($request->hasHeader('X-Multi'));
    }

    public function test_server_params_preservation() : void
    {
        $server  = ['REQUEST_METHOD' => 'POST', 'REMOTE_ADDR' => '127.0.0.1'];
        $request = $this->assembler->fromGlobals(server: $server);
        
        $this->assertEquals($server, $request->getServerParams());
    }

    public function test_with_uri_preserve_host_logic() : void
    {
        $request = $this->assembler->fromSlices(method: 'GET')
            ->withHeader('Host', 'old.com');
        
        $newUri = UriBuilder::createFromString('https://new.com/path');
        
        // preserveHost = false
        $r1 = $request->withUri($newUri, false);
        $this->assertEquals(['new.com'], $r1->getHeader('Host'));
        
        // preserveHost = true
        $r2 = $request->withUri($newUri, true);
        $this->assertEquals(['old.com'], $r2->getHeader('Host'));
    }

    public function test_attribute_management() : void
    {
        $request = $this->assembler->empty()
            ->withAttribute('user_id', 123)
            ->withAttribute('role', 'admin');
            
        $this->assertEquals(123, $request->getAttribute('user_id'));
        $this->assertEquals(['user_id' => 123, 'role' => 'admin'], $request->getAttributes());
        
        $request = $request->withoutAttribute('role');
        $this->assertNull($request->getAttribute('role'));
        $this->assertEquals('default', $request->getAttribute('role', 'default'));
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
