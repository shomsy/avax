<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Unit;

use Avax\HTTP\Request\Request;
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
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionException;

final class RequestAttachedDto extends Request {}

class RequestAttachedDtoTest extends TestCase
{
    private AssembleIncomingRequest $assembler;

    /**
     * @throws ReflectionException
     */
    public function test_from_request_exposes_attached_server_request_accessors(): void
    {
        $serverRequest = $this->createServerRequest();
        $request       = RequestAttachedDto::fromRequest(request: $serverRequest);

        $this->assertSame(expected: $serverRequest, actual: $request->serverRequest());
        $this->assertSame(expected: 'POST', actual: $request->method());
        $this->assertSame(expected: 'example.com', actual: $request->uri()->getHost());
        $this->assertSame(expected: 'value', actual: $request->header(name: 'X-Test'));
    }

    private function createServerRequest() : ServerRequest
    {
        return $this->assembler->fromSlices(
            queryParams: ['q' => 'search'],
            parsedBody : ['payload' => 'body'],
            method     : 'POST',
        )->withUri(uri: UriBuilder::createFromString(uri: 'https://example.com/api'))
            ->withHeader(name: 'X-Test', value: 'value');
    }

    protected function setUp() : void
    {
        $preparer = new PrepareRequest(
            bodyParser        : new ParseBodyByContentType(
                jsonParser: new ParseJsonBody,
                formParser: new ParseFormBody,
            ),
            protocolNormalizer: new NormalizeProtocolVersion,
            filesNormalizer   : new NormalizeUploadedFiles,
            trustedProxyPolicy: new TrustedIpv4ProxyPolicy,
            clientResolver    : new ResolveClientAddress(
                proxyPolicy    : new TrustedIpv4ProxyPolicy,
                forwardedParser: new ParseForwardedAddresses,
            )
        );

        $this->assembler = new AssembleIncomingRequest(
            preparer : $preparer,
            sanitizer: new InputSanitizer,
            mapper   : new MapRequestedInputsToDto,
        );
    }
}
