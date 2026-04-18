<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedProxyPolicy;
use PHPUnit\Framework\TestCase;

class CreateRequestTest extends TestCase
{
    private PrepareRequest $prepare;

    public function test_prepare_request_builds_request_init_from_explicit_inputs()
    {
        $server = [
            'REQUEST_METHOD'  => 'POST',
            'HTTP_HOST'       => 'api.example.com',
            'REQUEST_URI'     => '/v1/users',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ];

        $init = $this->prepare->fromGlobals(server: $server);

        $this->assertSame(expected: 'POST', actual: $init->method);
        $this->assertSame(expected: 'api.example.com', actual: $init->uri->getHost());
        $this->assertSame(expected: '/v1/users', actual: $init->uri->getPath());
        $this->assertSame(expected: '1.1', actual: $init->protocolVersion);
    }

    public function test_prepare_request_uses_query_cookie_and_files_arguments()
    {
        $server = ['REQUEST_METHOD' => 'GET', 'HTTP_HOST' => 'localhost'];
        $query  = ['foo' => 'bar'];
        $cookie = ['session' => 'abc'];

        $init = $this->prepare->fromGlobals(server: $server, query: $query, cookie: $cookie);

        $this->assertSame(expected: $query, actual: $init->queryParams);
        $this->assertSame(expected: $cookie, actual: $init->cookies->all());
    }

    public function test_prepare_request_normalizes_http_1_1_to_1_1()
    {
        $server = ['REQUEST_METHOD' => 'GET', 'SERVER_PROTOCOL' => 'HTTP/1.1'];

        $init = $this->prepare->fromGlobals(server: $server);

        $this->assertSame(expected: '1.1', actual: $init->protocolVersion);
    }

    public function test_prepare_request_defaults_protocol_version_to_1_1_when_missing()
    {
        $server = ['REQUEST_METHOD' => 'GET'];

        $init = $this->prepare->fromGlobals(server: $server);

        $this->assertSame(expected: '1.1', actual: $init->protocolVersion);
    }

    protected function setUp() : void
    {
        $this->prepare = new PrepareRequest(
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
    }
}
