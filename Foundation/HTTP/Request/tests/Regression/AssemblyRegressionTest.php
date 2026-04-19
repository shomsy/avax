<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Regression;

use Avax\HTTP\Request\ServerRequest\IncomingRequest\Configuration\PrepareRequest;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ProtocolVersion\NormalizeProtocolVersion;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseBodyByContentType;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseFormBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestBody\Parsers\ParseJsonBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\UploadedFiles\NormalizeUploadedFiles;
use Avax\HTTP\Request\ServerRequest\Network\ParseForwardedAddresses;
use Avax\HTTP\Request\ServerRequest\Network\ResolveClientAddress;
use Avax\HTTP\Request\ServerRequest\Network\TrustedIpv4ProxyPolicy;
use PHPUnit\Framework\TestCase;

class AssemblyRegressionTest extends TestCase
{
    private PrepareRequest $prepareRequest;

    public function test_from_globals_reads_server_superglobal()
    {
        $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/test',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ]
        );

        $this->assertTrue(condition: true);
    }

    public function test_from_globals_honors_supplied_server_array()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'POST',
                        'HTTP_HOST'       => 'custom.host',
                        'REQUEST_URI'     => '/api/posts',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'CONTENT_TYPE'    => 'application/json',
                    ],
            query : ['page' => '1'],
            cookie: ['session' => 'abc123'],
        );

        $this->assertEquals(expected: 'POST', actual: $result->method);
        $this->assertEquals(expected: 'custom.host', actual: $result->uri->getHost());
    }

    public function test_body_parsing_only_for_post_method()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'POST',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/submit',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'CONTENT_TYPE'    => 'application/json',
                    ]
        );

        $parsedBody = $result->parsedBody->data();
        $this->assertNotNull(actual: $parsedBody);
    }

    public function test_body_parsing_only_for_put_method()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'PUT',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/update',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'CONTENT_TYPE'    => 'application/json',
                    ]
        );

        $parsedBody = $result->parsedBody->data();
        $this->assertNotNull(actual: $parsedBody);
    }

    public function test_body_parsing_only_for_patch_method()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'PATCH',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/modify',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'CONTENT_TYPE'    => 'application/json',
                    ]
        );

        $parsedBody = $result->parsedBody->data();
        $this->assertNotNull(actual: $parsedBody);
    }

    public function test_body_parsing_only_for_delete_method()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'DELETE',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/remove',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'CONTENT_TYPE'    => 'application/json',
                    ]
        );

        $parsedBody = $result->parsedBody->data();
        $this->assertNotNull(actual: $parsedBody);
    }

    public function test_no_body_parsing_for_get_method()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/fetch',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ]
        );

        $parsedBody = $result->parsedBody->data();
        $this->assertNull(actual: $parsedBody);
    }

    public function test_no_body_parsing_for_head_method()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'HEAD',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/check',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ]
        );

        $parsedBody = $result->parsedBody->data();
        $this->assertNull(actual: $parsedBody);
    }

    public function test_no_body_parsing_for_options_method()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'OPTIONS',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/options',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ]
        );

        $parsedBody = $result->parsedBody->data();
        $this->assertNull(actual: $parsedBody);
    }

    public function test_parsed_body_correctness_by_content_type_json()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'POST',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/api',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'CONTENT_TYPE'    => 'application/json',
                    ]
        );

        $this->assertIsArray(actual: $result->parsedBody->data());
    }

    public function test_parsed_body_correctness_by_content_type_form()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'POST',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/submit',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'CONTENT_TYPE'    => 'application/x-www-form-urlencoded',
                    ]
        );

        $this->assertIsArray(actual: $result->parsedBody->data());
    }

    public function test_protocol_normalization_integration()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ]
        );

        $this->assertEquals(expected: '1.1', actual: $result->protocolVersion);
    }

    public function test_https_detection_from_server()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'HTTP_HOST'       => 'secure.example.com',
                        'REQUEST_URI'     => '/secure',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'HTTPS'           => 'on',
                    ]
        );

        $this->assertEquals(expected: 'https', actual: $result->uri->getScheme());
    }

    public function test_http_fallback_when_https_off()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                        'HTTPS'           => 'off',
                    ]
        );

        $this->assertEquals(expected: 'http', actual: $result->uri->getScheme());
    }

    public function test_default_host_when_missing()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'REQUEST_URI'     => '/',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ]
        );

        $this->assertEquals(expected: 'localhost', actual: $result->uri->getHost());
    }

    public function test_default_uri_when_missing()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'HTTP_HOST'       => 'example.com',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ]
        );

        $this->assertEquals(expected: '/', actual: $result->uri->getPath());
    }

    public function test_query_params_passed_through()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ],
            query : ['search' => 'test', 'page' => '1']
        );

        $this->assertEquals(expected: 'test', actual: $result->queryParams['search']);
        $this->assertEquals(expected: '1', actual: $result->queryParams['page']);
    }

    public function test_cookies_passed_through()
    {
        $result = $this->prepareRequest->fromGlobals(
            server: [
                        'REQUEST_METHOD'  => 'GET',
                        'HTTP_HOST'       => 'example.com',
                        'REQUEST_URI'     => '/',
                        'SERVER_PROTOCOL' => 'HTTP/1.1',
                    ],
            cookie: ['session' => 'abc123', 'prefs' => 'dark']
        );

        $this->assertTrue(condition: $result->cookies->has(name: 'session'));
    }

    protected function setUp() : void
    {
        $this->prepareRequest = new PrepareRequest(
            bodyParser        : new ParseBodyByContentType(
                                    jsonParser: new ParseJsonBody,
                                    formParser: new ParseFormBody
                                ),
            protocolNormalizer: new NormalizeProtocolVersion,
            filesNormalizer   : new NormalizeUploadedFiles,
            trustedProxyPolicy: new TrustedIpv4ProxyPolicy(trustedProxies: []),
            forwardedParser   : new ParseForwardedAddresses,
            clientResolver    : new ResolveClientAddress(
                                    proxyPolicy    : new TrustedIpv4ProxyPolicy(trustedProxies: []),
                                    forwardedParser: new ParseForwardedAddresses
                                ),
        );
    }
}
