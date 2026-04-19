<?php

declare(strict_types=1);

namespace Avax\HTTP\Tests\Foundation\Request;

use Avax\HTTP\Request\AbsoluteServerRequest;
use Avax\HTTP\URI\UriBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

final class AbsoluteServerRequestTest extends TestCase
{
    public function test_implements_server_request_interface() : void
    {
        $request = $this->createRequest();

        $this->assertInstanceOf(expected: ServerRequestInterface::class, actual: $request);
    }

    private function createRequest(
        array|null           $serverParams = null,
        UriInterface|null    $uri = null,
        StreamInterface|null $body = null,
        array|null           $queryParams = null,
        array|null           $parsedBody = null,
        array|null           $cookies = null,
        array                $uploadedFiles = []
    ) : AbsoluteServerRequest
    {
        $serverParams ??= ['REQUEST_METHOD' => 'GET'];
        $queryParams  ??= [];
        $parsedBody   ??= [];
        $cookies      ??= [];
        $defaultUri   = UriBuilder::createFromString(uri: 'http://localhost/test');

        return new AbsoluteServerRequest(
            server       : $serverParams,
            uri          : $uri ?? $defaultUri,
            body         : $body,
            queryParams  : $queryParams,
            parsedBody   : $parsedBody,
            cookies      : $cookies,
            uploadedFiles: $uploadedFiles
        );
    }

    public function test_get_method_returns_http_method() : void
    {
        $request = $this->createRequest(serverParams: ['REQUEST_METHOD' => 'POST']);

        $this->assertSame(expected: 'POST', actual: $request->getMethod());
    }

    public function test_get_method_defaults_to_get() : void
    {
        $request = $this->createRequest(serverParams: []);

        $this->assertSame(expected: 'GET', actual: $request->getMethod());
    }

    public function test_with_method_returns_cloned_instance() : void
    {
        $request    = $this->createRequest();
        $newRequest = $request->withMethod('PUT');

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertSame(expected: 'GET', actual: $request->getMethod());
        $this->assertSame(expected: 'PUT', actual: $newRequest->getMethod());
    }

    public function test_get_uri_returns_uri() : void
    {
        $uri     = UriBuilder::createFromString(uri: 'https://example.com/test/path?foo=bar');
        $request = $this->createRequest(serverParams: [], uri: $uri);

        $this->assertSame(expected: $uri, actual: $request->getUri());
    }

    public function test_with_uri_returns_cloned_instance() : void
    {
        $request    = $this->createRequest();
        $newUri     = UriBuilder::createFromString(uri: 'https://example.com/new/path');
        $newRequest = $request->withUri($newUri);

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertSame(expected: '/new/path', actual: $newRequest->getUri()->getPath());
    }

    public function test_get_server_params_returns_server_array() : void
    {
        $serverParams = ['REQUEST_METHOD' => 'POST', 'HTTP_HOST' => 'example.com'];
        $request      = $this->createRequest(serverParams: $serverParams);

        $this->assertSame(expected: $serverParams, actual: $request->getServerParams());
    }

    public function test_get_query_params_returns_query_array() : void
    {
        $request = $this->createRequest(serverParams: [], uri: null, body: null, queryParams: ['foo' => 'bar', 'baz' => 'qux']);

        $this->assertSame(expected: ['foo' => 'bar', 'baz' => 'qux'], actual: $request->getQueryParams());
    }

    public function test_with_query_params_returns_cloned_instance() : void
    {
        $request    = $this->createRequest(serverParams: [], uri: null, body: null, queryParams: ['foo' => 'bar']);
        $newRequest = $request->withQueryParams(['baz' => 'qux']);

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertSame(expected: ['foo' => 'bar'], actual: $request->getQueryParams());
        $this->assertSame(expected: ['baz' => 'qux'], actual: $newRequest->getQueryParams());
    }

    public function test_get_parsed_body_returns_body_array() : void
    {
        $request = $this->createRequest(serverParams: [], uri: null, body: null, queryParams: [], parsedBody: ['name' => 'John']);

        $this->assertSame(expected: ['name' => 'John'], actual: $request->getParsedBody());
    }

    public function test_with_parsed_body_returns_cloned_instance() : void
    {
        $request    = $this->createRequest(serverParams: [], uri: null, body: null, queryParams: [], parsedBody: ['name' => 'John']);
        $newRequest = $request->withParsedBody(['name' => 'Jane']);

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertSame(expected: ['name' => 'John'], actual: $request->getParsedBody());
        $this->assertSame(expected: ['name' => 'Jane'], actual: $newRequest->getParsedBody());
    }

    public function test_get_cookie_params_returns_cookies_array() : void
    {
        $request = $this->createRequest(serverParams: [], uri: null, body: null, queryParams: [], parsedBody: [], cookies: ['session' => 'abc123']);

        $this->assertSame(expected: ['session' => 'abc123'], actual: $request->getCookieParams());
    }

    public function test_with_cookie_params_returns_cloned_instance() : void
    {
        $request    = $this->createRequest(serverParams: [], uri: null, body: null, queryParams: [], parsedBody: [], cookies: ['session' => 'abc123']);
        $newRequest = $request->withCookieParams(['session' => 'xyz789']);

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertSame(expected: ['session' => 'abc123'], actual: $request->getCookieParams());
        $this->assertSame(expected: ['session' => 'xyz789'], actual: $newRequest->getCookieParams());
    }

    public function test_get_uploaded_files_returns_files_array() : void
    {
        $uploadedFiles = $this->createMockUploadedFiles();
        $request       = $this->createRequest(serverParams: [], uri: null, body: null, queryParams: [], parsedBody: [], cookies: [], uploadedFiles: $uploadedFiles);

        $this->assertSame(expected: $uploadedFiles, actual: $request->getUploadedFiles());
    }

    private function createMockUploadedFiles() : array
    {
        $mock = $this->createMock(UploadedFileInterface::class);

        return ['file' => $mock];
    }

    public function test_with_uploaded_files_throws_for_invalid_type() : void
    {
        $request = $this->createRequest();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Uploaded files must implement UploadedFileInterface');

        $request->withUploadedFiles(['not-an-uploaded-file']);
    }

    /**
     * @group characterization
     * @group bug-getHeader-returns-string
     *
     * BUG: getHeader() returns string instead of array when header is set via constructor
     */
    public function test_get_header_returns_array_of_values() : void
    {
        $request = $this->createRequest(serverParams: [
                                                          'HTTP_CONTENT_TYPE' => 'application/json',
                                                      ]);

        $header = $request->getHeader('Content-Type');
        $this->assertIsArray(actual: $header);
        $this->assertSame(expected: ['application/json'], actual: $header);
    }

    /**
     * @group characterization
     */
    public function test_get_header_returns_empty_array_for_missing_header() : void
    {
        $request = $this->createRequest(serverParams: []);

        $this->assertSame(expected: [], actual: $request->getHeader('X-Custom-Header'));
    }

    /**
     * @group characterization
     * @group bug-getHeader-returns-string
     */
    public function test_get_header_line_returns_comma_separated_string() : void
    {
        $request = $this->createRequest(serverParams: [
                                                          'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
                                                      ]);

        $this->assertSame(expected: 'en-US,en;q=0.9', actual: $request->getHeaderLine('Accept-Language'));
    }

    public function test_get_header_line_returns_empty_string_for_missing_header() : void
    {
        $request = $this->createRequest(serverParams: []);

        $this->assertSame(expected: '', actual: $request->getHeaderLine('X-Custom-Header'));
    }

    public function test_has_header_returns_true_for_existing_header() : void
    {
        $request = $this->createRequest(serverParams: ['HTTP_CONTENT_TYPE' => 'application/json']);

        $this->assertTrue(condition: $request->hasHeader('Content-Type'));
    }

    public function test_has_header_returns_false_for_missing_header() : void
    {
        $request = $this->createRequest(serverParams: []);

        $this->assertFalse(condition: $request->hasHeader('X-Custom-Header'));
    }

    public function test_with_header_returns_cloned_instance() : void
    {
        $request    = $this->createRequest(serverParams: []);
        $newRequest = $request->withHeader('X-Custom', 'value');

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertTrue(condition: $newRequest->hasHeader('X-Custom'));
        $this->assertFalse(condition: $request->hasHeader('X-Custom'));
    }

    /**
     * @group characterization
     * @group bug-getHeader-returns-string
     */
    public function test_with_added_header_appends_to_existing() : void
    {
        $request    = $this->createRequest(serverParams: ['HTTP_X_CUSTOM' => 'first']);
        $newRequest = $request->withAddedHeader('X-Custom', 'second');

        $this->assertSame(expected: ['first', 'second'], actual: $newRequest->getHeader('X-Custom'));
    }

    public function test_without_header_removes_header() : void
    {
        $request    = $this->createRequest(serverParams: ['HTTP_CONTENT_TYPE' => 'application/json']);
        $newRequest = $request->withoutHeader('Content-Type');

        $this->assertFalse(condition: $newRequest->hasHeader('Content-Type'));
        $this->assertTrue(condition: $request->hasHeader('Content-Type'));
    }

    public function test_get_headers_returns_all_headers() : void
    {
        $request = $this->createRequest(serverParams: [
                                                          'HTTP_CONTENT_TYPE' => 'application/json',
                                                          'HTTP_ACCEPT'       => 'text/html',
                                                      ]);

        $headers = $request->getHeaders();

        $this->assertArrayHasKey(key: 'Content-Type', array: $headers);
        $this->assertArrayHasKey(key: 'Accept', array: $headers);
    }

    public function test_get_body_returns_stream() : void
    {
        $request = $this->createRequest();

        $this->assertInstanceOf(expected: StreamInterface::class, actual: $request->getBody());
    }

    public function test_with_body_returns_cloned_instance() : void
    {
        $request    = $this->createRequest();
        $body       = $this->createMock(StreamInterface::class);
        $newRequest = $request->withBody($body);

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertSame(expected: $body, actual: $newRequest->getBody());
    }

    public function test_get_protocol_version() : void
    {
        $request = $this->createRequest(serverParams: ['SERVER_PROTOCOL' => '2.0']);

        $this->assertSame(expected: '2.0', actual: $request->getProtocolVersion());
    }

    public function test_with_protocol_version_returns_cloned_instance() : void
    {
        $request    = $this->createRequest();
        $newRequest = $request->withProtocolVersion('2.0');

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertSame(expected: '2.0', actual: $newRequest->getProtocolVersion());
        $this->assertSame(expected: '1.1', actual: $request->getProtocolVersion());
    }

    public function test_get_request_target() : void
    {
        $uri     = UriBuilder::createFromString(uri: 'https://example.com/test/path?foo=bar');
        $request = $this->createRequest(serverParams: [], uri: $uri);

        $this->assertSame(expected: '/test/path?foo=bar', actual: $request->getRequestTarget());
    }

    public function test_with_request_target_returns_cloned_instance() : void
    {
        $request    = $this->createRequest();
        $newRequest = $request->withRequestTarget('/new/target');

        $this->assertNotSame(expected: $request, actual: $newRequest);
        $this->assertSame(expected: '/new/target', actual: $newRequest->getRequestTarget());
    }

    public function test_get_attributes_returns_empty_array_by_default() : void
    {
        $request = $this->createRequest();

        $this->assertSame(expected: [], actual: $request->getAttributes());
    }

    public function test_with_attribute_sets_attribute() : void
    {
        $request    = $this->createRequest();
        $newRequest = $request->withAttribute('user_id', 123);

        $this->assertSame(expected: 123, actual: $newRequest->getAttribute('user_id'));
        $this->assertNull(actual: $request->getAttribute('user_id'));
    }

    public function test_without_attribute_removes_attribute() : void
    {
        $request     = $this->createRequest();
        $withAttr    = $request->withAttribute('user_id', 123);
        $withoutAttr = $withAttr->withoutAttribute('user_id');

        $this->assertNull(actual: $withoutAttr->getAttribute('user_id'));
    }

    public function test_get_attribute_returns_default_when_missing() : void
    {
        $request = $this->createRequest();

        $this->assertSame(expected: 'default', actual: $request->getAttribute('missing', 'default'));
    }

    public function test_route_is_alias_for_get_attribute() : void
    {
        $request = $this->createRequest()->withAttribute('user_id', 456);

        $this->assertSame(expected: 456, actual: $request->route('user_id'));
        $this->assertSame(expected: 'default', actual: $request->route('missing', 'default'));
    }

    public function test_get_client_ip_returns_ip_from_remote_addr() : void
    {
        $request = $this->createRequest(serverParams: ['REMOTE_ADDR' => '192.168.1.100']);

        $this->assertSame(expected: '192.168.1.100', actual: $request->getClientIp());
    }

    public function test_get_client_ip_prefers_forwarded_headers() : void
    {
        $request = $this->createRequest(serverParams: [
                                                          'HTTP_X_FORWARDED_FOR' => '10.0.0.1, 192.168.1.1',
                                                          'REMOTE_ADDR'          => '127.0.0.1',
                                                      ]);

        $this->assertSame(expected: '10.0.0.1', actual: $request->getClientIp());
    }

    public function test_get_client_ip_handles_multiple_proxies() : void
    {
        $request = $this->createRequest(serverParams: [
                                                          'HTTP_CLIENT_IP' => '203.0.113.50',
                                                          'REMOTE_ADDR'    => '10.0.0.1',
                                                      ]);

        $this->assertSame(expected: '203.0.113.50', actual: $request->getClientIp());
    }

    public function test_get_client_ip_returns_null_when_no_ip_found() : void
    {
        $request = $this->createRequest(serverParams: []);

        $this->assertNull(actual: $request->getClientIp());
    }
}
