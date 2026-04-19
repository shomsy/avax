<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

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
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ServerRequestCorrectnessTest extends TestCase
{
    private AssembleIncomingRequest $assembler;

    public function test_with_uploaded_files_rejects_invalid_tree()
    {
        $request = $this->createServerRequest();

        $this->expectException(InvalidArgumentException::class);
        $request->withUploadedFiles(uploadedFiles: ['invalid' => 'not-a-file-object']);
    }

    private function createServerRequest() : ServerRequest
    {
        return $this->assembler->fromSlices(
            queryParams: [],
            parsedBody : null,
            method     : 'GET'
        )->withUri(
            uri         : UriBuilder::createFromString(uri: 'http://localhost/'),
            preserveHost: false
        )->withHeader(name: 'Host', value: 'localhost');
    }

    public function test_with_uri_sets_host_when_preserve_host_is_false()
    {
        $request = $this->createServerRequest();
        $newUri  = UriBuilder::createFromString(uri: 'https://example.com/foo');

        $newRequest = $request->withUri(uri: $newUri, preserveHost: false);

        $this->assertEquals(expected: ['example.com'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_with_uri_preserves_existing_host_when_requested()
    {
        $request = $this->createServerRequest();
        $newUri  = UriBuilder::createFromString(uri: 'https://example.com/foo');

        $newRequest = $request->withUri(uri: $newUri, preserveHost: true);

        $this->assertEquals(expected: ['localhost'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_with_uri_handles_empty_host_safely()
    {
        $request = $this->createServerRequest();
        $newUri  = UriBuilder::createFromString(uri: 'http://localhost/path-only');

        $newRequest = $request->withUri(uri: $newUri, preserveHost: false);

        $this->assertEquals(expected: ['localhost'], actual: $newRequest->getHeader(name: 'Host'));
    }

    public function test_request_target_falls_back_to_path_and_query()
    {
        $uri     = UriBuilder::createFromString(uri: 'http://localhost/foo?bar=baz');
        $request = $this->assembler->fromSlices(
            queryParams: [],
            parsedBody : null,
            method     : 'GET'
        )->withUri(uri: $uri);

        $this->assertEquals(expected: '/foo?bar=baz', actual: $request->requestTarget);
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

