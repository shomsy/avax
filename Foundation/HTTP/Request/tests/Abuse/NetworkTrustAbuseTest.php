<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Abuse;

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
use PHPUnit\Framework\TestCase;

/**
 * NetworkTrustAbuseTest - Protecting against trust spoofing and malformed network data.
 */
class NetworkTrustAbuseTest extends TestCase
{
    private AssembleIncomingRequest $assembler;

    public function test_untrusted_proxy_spoofing_is_ignored() : void
    {
        $server = [
            'REMOTE_ADDR'          => '203.0.113.1', // Untrusted public IP
            'HTTP_X_FORWARDED_FOR' => '10.0.0.5, 192.168.1.1', // Attempted spoof
        ];

        $request = $this->assembler->fromGlobals(server: $server);
        
        $this->assertEquals('203.0.113.1', $request->resolveClientAddress($server));
    }

    public function test_trusted_proxy_chain_resolution() : void
    {
        $server = [
            'REMOTE_ADDR'          => '127.0.0.1', // Trusted local proxy
            'HTTP_X_FORWARDED_FOR' => '203.0.113.5', // Actual client IP
        ];

        $request = $this->assembler->fromGlobals(server: $server);
        
        // With default policy trusting 127.0.0.1
        $this->assertEquals('203.0.113.5', $request->resolveClientAddress($server));
    }

    public function test_malformed_forwarded_header_fails_gracefully() : void
    {
        $server = [
            'REMOTE_ADDR'          => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => 'invalid-ip-address, @#$%^',
        ];

        $request = $this->assembler->fromGlobals(server: $server);
        
        // Should fall back to remote addr or return null if totally broken
        $clientIp = $request->resolveClientAddress($server);
        $this->assertTrue($clientIp === '127.0.0.1' || $clientIp === null);
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
            trustedProxyPolicy: new TrustedProxyPolicy(trustedProxies: ['127.0.0.1']),
            forwardedParser   : new ParseForwardedAddresses,
            clientResolver    : new ResolveClientAddress(
                                    proxyPolicy    : new TrustedProxyPolicy(trustedProxies: ['127.0.0.1']),
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
