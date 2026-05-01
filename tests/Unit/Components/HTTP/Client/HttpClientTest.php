<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Client;

use Avax\Components\HTTP\Client\System\Capabilities\Middleware\ClientMiddlewareInterface;
use Avax\Components\HTTP\Client\System\Capabilities\Middleware\ClientMiddlewarePipeline;
use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Requests\RequestOptions;
use Avax\Components\HTTP\Client\System\Capabilities\Resilience\RetryPolicy;
use Avax\Components\HTTP\Client\System\Capabilities\Resilience\TimeoutPolicy;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ResponseDecoder;
use Avax\Components\HTTP\Client\System\Capabilities\Testing\FakeHttpClient;
use Avax\Components\HTTP\Client\System\Capabilities\Testing\RecordedHttpResponse;
use Avax\Components\HTTP\Client\System\Capabilities\Transports\CurlTransport;
use Avax\Components\HTTP\Client\System\Capabilities\Transports\HttpTransportInterface;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpTimeout;
use Avax\Components\HTTP\Client\System\Foundation\Failure\InvalidHttpResponse;
use Closure;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleXMLElement;

/**
 * Comprehensive tests for the HTTP Client component.
 */
final class HttpClientTest extends TestCase
{
    // ==========================================
    // 1. OutboundRequest Tests
    // ==========================================

    #[Test]
    public function outbound_request_creation_with_defaults() : void
    {
        $request = new OutboundRequest;

        $this->assertEquals('GET', $request->method);
        $this->assertEquals('', $request->url);
        $this->assertNull($request->body);
        $this->assertEquals([], $request->headers);
        $this->assertNull($request->options);
        $this->assertEquals([], $request->context);
    }

    #[Test]
    public function outbound_request_creation_with_custom_values() : void
    {
        $options = new RequestOptions(timeout: 5000);
        $request = new OutboundRequest(
            method : 'POST',
            url    : 'https://api.example.com/users',
            body   : '{"name":"John"}',
            headers: ['Content-Type' => 'application/json'],
            options: $options,
            context: ['source' => 'test'],
        );

        $this->assertEquals('POST', $request->method);
        $this->assertEquals('https://api.example.com/users', $request->url);
        $this->assertEquals('{"name":"John"}', $request->body);
        $this->assertEquals(['Content-Type' => 'application/json'], $request->headers);
        $this->assertSame($options, $request->options);
        $this->assertEquals(['source' => 'test'], $request->context);
    }

    #[Test]
    public function outbound_request_with_method_returns_new_instance() : void
    {
        $opts = new RequestOptions;
        $original = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);
        $modified = $original->withMethod('POST');

        $this->assertNotSame($original, $modified);
        $this->assertEquals('GET', $original->method);
        $this->assertEquals('POST', $modified->method);
        $this->assertEquals('https://example.com', $modified->url);
    }

    #[Test]
    public function outbound_request_with_url_returns_new_instance() : void
    {
        $opts = new RequestOptions;
        $original = new OutboundRequest(method: 'GET', url: 'https://example.com/old', options: $opts);
        $modified = $original->withUrl('https://example.com/new');

        $this->assertNotSame($original, $modified);
        $this->assertEquals('https://example.com/old', $original->url);
        $this->assertEquals('https://example.com/new', $modified->url);
        $this->assertEquals('GET', $modified->method);
    }

    #[Test]
    public function outbound_request_with_body_returns_new_instance() : void
    {
        $opts = new RequestOptions;
        $original = new OutboundRequest(options: $opts);
        $modified = $original->withBody('{"data":"test"}');

        $this->assertNotSame($original, $modified);
        $this->assertNull($original->body);
        $this->assertEquals('{"data":"test"}', $modified->body);
    }

    #[Test]
    public function outbound_request_with_headers_merges_correctly() : void
    {
        $opts = new RequestOptions;
        $original = new OutboundRequest(headers: ['Accept' => 'application/json'], options: $opts);
        $modified = $original->withHeaders(['Content-Type' => 'application/json', 'X-Custom' => 'value']);

        $this->assertNotSame($original, $modified);
        $this->assertEquals(['Accept' => 'application/json'], $original->headers);
        $this->assertEquals([
                                'Accept'       => 'application/json',
                                'Content-Type' => 'application/json',
                                'X-Custom'     => 'value',
                            ], $modified->headers);
    }

    #[Test]
    public function outbound_request_with_options_returns_new_instance() : void
    {
        $opts     = new RequestOptions;
        $original = new OutboundRequest(options: $opts);
        $newOptions = new RequestOptions(timeout: 10000);
        $modified = $original->withOptions($newOptions);

        $this->assertNotSame($original, $modified);
        $this->assertEquals(RequestOptions::DEFAULT_TIMEOUT, $original->options->timeout);
        $this->assertEquals(10000, $modified->options->timeout);
    }

    #[Test]
    public function outbound_request_with_context_merges_correctly() : void
    {
        $opts = new RequestOptions;
        $original = new OutboundRequest(context: ['key1' => 'value1'], options: $opts);
        $modified = $original->withContext(['key2' => 'value2']);

        $this->assertNotSame($original, $modified);
        $this->assertEquals(['key1' => 'value1'], $original->context);
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $modified->context);
    }

    #[Test]
    public function outbound_request_get_header_returns_value() : void
    {
        $opts = new RequestOptions;
        $request = new OutboundRequest(headers: ['Content-Type' => 'application/json', 'X-Request-ID' => 'abc123'], options: $opts);

        $this->assertEquals('application/json', $request->getHeader('Content-Type'));
        $this->assertEquals('abc123', $request->getHeader('X-Request-ID'));
        $this->assertNull($request->getHeader('Non-Existent'));
    }

    #[Test]
    public function outbound_request_has_body_returns_correctly() : void
    {
        $opts               = new RequestOptions;
        $requestWithoutBody = new OutboundRequest(options: $opts);
        $requestWithBody    = new OutboundRequest(body: 'test body', options: $opts);
        $requestWithEmptyString = new OutboundRequest(body: '', options: $opts);

        $this->assertFalse($requestWithoutBody->hasBody());
        $this->assertTrue($requestWithBody->hasBody());
        $this->assertTrue($requestWithEmptyString->hasBody());
    }

    #[Test]
    public function outbound_request_expects_json_checks_accept_header() : void
    {
        $opts         = new RequestOptions;
        $jsonRequest  = new OutboundRequest(headers: ['Accept' => 'application/json'], options: $opts);
        $plainRequest = new OutboundRequest(headers: ['Accept' => 'text/html'], options: $opts);
        $mixedRequest = new OutboundRequest(headers: ['Accept' => 'text/html, application/json'], options: $opts);
        $noAcceptRequest = new OutboundRequest(options: $opts);

        $this->assertTrue($jsonRequest->expectsJson());
        $this->assertFalse($plainRequest->expectsJson());
        $this->assertTrue($mixedRequest->expectsJson());
        $this->assertFalse($noAcceptRequest->expectsJson());
    }

    #[Test]
    public function outbound_request_is_safe_for_get_head_options() : void
    {
        $opts          = new RequestOptions;
        $getRequest    = new OutboundRequest(method: 'GET', options: $opts);
        $headRequest   = new OutboundRequest(method: 'HEAD', options: $opts);
        $optionsRequest = new OutboundRequest(method: 'OPTIONS', options: $opts);
        $postRequest   = new OutboundRequest(method: 'POST', options: $opts);
        $deleteRequest = new OutboundRequest(method: 'DELETE', options: $opts);

        $this->assertTrue($getRequest->isSafe());
        $this->assertTrue($headRequest->isSafe());
        $this->assertTrue($optionsRequest->isSafe());
        $this->assertFalse($postRequest->isSafe());
        $this->assertFalse($deleteRequest->isSafe());
    }

    #[Test]
    public function outbound_request_is_idempotent_for_correct_methods() : void
    {
        $opts         = new RequestOptions;
        $getRequest   = new OutboundRequest(method: 'GET', options: $opts);
        $putRequest   = new OutboundRequest(method: 'PUT', options: $opts);
        $deleteRequest = new OutboundRequest(method: 'DELETE', options: $opts);
        $postRequest  = new OutboundRequest(method: 'POST', options: $opts);
        $patchRequest = new OutboundRequest(method: 'PATCH', options: $opts);

        $this->assertTrue($getRequest->isIdempotent());
        $this->assertTrue($putRequest->isIdempotent());
        $this->assertTrue($deleteRequest->isIdempotent());
        $this->assertFalse($postRequest->isIdempotent());
        $this->assertFalse($patchRequest->isIdempotent());
    }

    #[Test]
    public function outbound_request_method_case_insensitive_for_safe_check() : void
    {
        $opts = new RequestOptions;
        $lowerCaseGet = new OutboundRequest(method: 'get', options: $opts);
        $upperCaseGet = new OutboundRequest(method: 'GET', options: $opts);

        $this->assertTrue($lowerCaseGet->isSafe());
        $this->assertTrue($upperCaseGet->isSafe());
    }

    #[Test]
    public function outbound_request_immutability_chain() : void
    {
        $opts = new RequestOptions;
        $original = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);

        $step1 = $original->withMethod('POST');
        $step2 = $step1->withUrl('https://example.com/api');
        $step3 = $step2->withBody('{"test":true}');
        $step4 = $step3->withHeaders(['X-Custom' => 'value']);

        $this->assertEquals('GET', $original->method);
        $this->assertEquals('https://example.com', $original->url);
        $this->assertNull($original->body);

        $this->assertEquals('POST', $step4->method);
        $this->assertEquals('https://example.com/api', $step4->url);
        $this->assertEquals('{"test":true}', $step4->body);
        $this->assertEquals(['X-Custom' => 'value'], $step4->headers);
    }

    // ==========================================
    // 2. RequestOptions Tests
    // ==========================================

    #[Test]
    public function request_options_default_values() : void
    {
        $options = new RequestOptions;

        $this->assertEquals(30_000, $options->timeout);
        $this->assertEquals(10_000, $options->connectTimeout);
        $this->assertTrue($options->verifySsl);
        $this->assertNull($options->sslCertPath);
        $this->assertNull($options->sslKeyPath);
        $this->assertNull($options->proxy);
        $this->assertNull($options->proxyAuth);
        $this->assertTrue($options->followRedirects);
        $this->assertEquals(5, $options->maxRedirects);
        $this->assertFalse($options->httpErrors);
        $this->assertNull($options->encoding);
        $this->assertNull($options->retryPolicy);
        $this->assertNull($options->timeoutPolicy);
        $this->assertEquals([], $options->additional);
    }

    #[Test]
    public function request_options_with_timeout_factory() : void
    {
        $options = RequestOptions::withTimeout(5000);
        $this->assertEquals(5000, $options->timeout);
        $this->assertEquals(10_000, $options->connectTimeout);
        $this->assertTrue($options->verifySsl);
    }

    #[Test]
    public function request_options_insecure_factory() : void
    {
        $options = RequestOptions::insecure();
        $this->assertFalse($options->verifySsl);
        $this->assertEquals(30_000, $options->timeout);
    }

    #[Test]
    public function request_options_with_retry_factory() : void
    {
        $retryPolicy = RetryPolicy::exponential(attempts: 3);
        $options = RequestOptions::withRetry($retryPolicy);
        $this->assertSame($retryPolicy, $options->retryPolicy);
        $this->assertTrue($options->hasRetry());
    }

    #[Test]
    public function request_options_with_proxy_factory() : void
    {
        $options = RequestOptions::withProxy('http://proxy.example.com:8080', 'user:pass');
        $this->assertEquals('http://proxy.example.com:8080', $options->proxy);
        $this->assertEquals('user:pass', $options->proxyAuth);
    }

    #[Test]
    public function request_options_with_proxy_without_auth() : void
    {
        $options = RequestOptions::withProxy('http://proxy.example.com:8080');
        $this->assertEquals('http://proxy.example.com:8080', $options->proxy);
        $this->assertNull($options->proxyAuth);
    }

    #[Test]
    public function request_options_no_redirects_factory() : void
    {
        $options = RequestOptions::noRedirects();
        $this->assertFalse($options->followRedirects);
        $this->assertEquals(5, $options->maxRedirects);
    }

    #[Test]
    public function request_options_merge_overrides_values() : void
    {
        $base   = new RequestOptions(timeout: 30_000, verifySsl: true, followRedirects: true);
        $override = new RequestOptions(timeout: 5000, verifySsl: false, followRedirects: false);
        $merged = $base->merge($override);

        $this->assertEquals(5000, $merged->timeout);
        $this->assertFalse($merged->verifySsl);
        $this->assertFalse($merged->followRedirects);
    }

    #[Test]
    public function request_options_merge_preserves_base_values() : void
    {
        $base   = new RequestOptions(timeout: 30_000, connectTimeout: 15_000, maxRedirects: 10);
        $override = new RequestOptions(timeout: 5000);
        $merged = $base->merge($override);

        $this->assertEquals(5000, $merged->timeout);
        $this->assertEquals(15_000, $merged->connectTimeout);
        $this->assertEquals(10, $merged->maxRedirects);
    }

    #[Test]
    public function request_options_merge_handles_nullable_fields() : void
    {
        $base   = new RequestOptions(proxy: 'http://old-proxy:8080', sslCertPath: '/old/cert.pem');
        $override = new RequestOptions(proxy: 'http://new-proxy:8080');
        $merged = $base->merge($override);

        $this->assertEquals('http://new-proxy:8080', $merged->proxy);
        $this->assertEquals('/old/cert.pem', $merged->sslCertPath);
    }

    #[Test]
    public function request_options_merge_combines_additional_array() : void
    {
        $base   = new RequestOptions(additional: ['key1' => 'value1']);
        $override = new RequestOptions(additional: ['key2' => 'value2']);
        $merged = $base->merge($override);

        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $merged->additional);
    }

    #[Test]
    public function request_options_effective_timeout_without_policy() : void
    {
        $options = new RequestOptions(timeout: 15000);
        $this->assertEquals(15000, $options->effectiveTimeout());
    }

    #[Test]
    public function request_options_effective_timeout_with_policy() : void
    {
        $timeoutPolicy = TimeoutPolicy::strict(5000);
        $options = new RequestOptions(timeout: 30_000, timeoutPolicy: $timeoutPolicy);
        $this->assertEquals(5000, $options->effectiveTimeout());
    }

    #[Test]
    public function request_options_get_returns_additional_value() : void
    {
        $options = new RequestOptions(additional: ['custom_key' => 'custom_value', 'another' => 123]);
        $this->assertEquals('custom_value', $options->get('custom_key'));
        $this->assertEquals(123, $options->get('another'));
        $this->assertNull($options->get('non_existent'));
        $this->assertEquals('default', $options->get('non_existent', 'default'));
    }

    #[Test]
    public function request_options_has_retry_returns_false_without_policy() : void
    {
        $options = new RequestOptions;
        $this->assertFalse($options->hasRetry());
    }

    // ==========================================
    // 3. ClientResponse Tests
    // ==========================================

    #[Test]
    public function client_response_creation_with_defaults() : void
    {
        $response = new ClientResponse;
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals([], $response->headers);
        $this->assertEquals('', $response->body);
        $this->assertEquals('OK', $response->reasonPhrase);
        $this->assertEquals('1.1', $response->protocol);
        $this->assertEquals(0.0, $response->transferTimeMs);
        $this->assertEquals(0.0, $response->connectTimeMs);
        $this->assertEquals(0.0, $response->totalTimeMs);
        $this->assertEquals(0, $response->redirectCount);
        $this->assertNull($response->effectiveUrl);
    }

    #[Test]
    public function client_response_creation_with_custom_values() : void
    {
        $response = new ClientResponse(
            statusCode    : 404,
            headers       : ['Content-Type' => ['application/json']],
            body          : '{"error":"Not Found"}',
            reasonPhrase  : 'Not Found',
            protocol      : '2.0',
            transferTimeMs: 150.5,
            connectTimeMs : 50.2,
            totalTimeMs   : 200.7,
            redirectCount : 1,
            effectiveUrl  : 'https://example.com/final',
        );

        $this->assertEquals(404, $response->statusCode);
        $this->assertEquals(['Content-Type' => ['application/json']], $response->headers);
        $this->assertEquals('{"error":"Not Found"}', $response->body);
        $this->assertEquals('Not Found', $response->reasonPhrase);
        $this->assertEquals('2.0', $response->protocol);
        $this->assertEquals(150.5, $response->transferTimeMs);
        $this->assertEquals(50.2, $response->connectTimeMs);
        $this->assertEquals(200.7, $response->totalTimeMs);
        $this->assertEquals(1, $response->redirectCount);
        $this->assertEquals('https://example.com/final', $response->effectiveUrl);
    }

    #[Test]
    public function client_response_from_raw_normalizes_headers() : void
    {
        $response = ClientResponse::fromRaw(
            statusCode: 200,
            headers   : ['Content-Type' => 'application/json', 'X-Custom' => ['value1', 'value2']],
            body      : '{"test":true}',
        );

        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(['application/json'], $response->headers['Content-Type']);
        $this->assertEquals(['value1', 'value2'], $response->headers['X-Custom']);
        $this->assertEquals('{"test":true}', $response->body);
    }

    #[Test]
    public function client_response_from_raw_with_reason_phrase() : void
    {
        $response = ClientResponse::fromRaw(statusCode: 201, body: '', reasonPhrase: 'Created');
        $this->assertEquals('Created', $response->reasonPhrase);
    }

    #[Test]
    public function client_response_is_successful_for_2xx_codes() : void
    {
        $this->assertTrue((new ClientResponse(statusCode: 200))->isSuccessful());
        $this->assertTrue((new ClientResponse(statusCode: 201))->isSuccessful());
        $this->assertTrue((new ClientResponse(statusCode: 204))->isSuccessful());
        $this->assertTrue((new ClientResponse(statusCode: 299))->isSuccessful());
        $this->assertFalse((new ClientResponse(statusCode: 199))->isSuccessful());
        $this->assertFalse((new ClientResponse(statusCode: 300))->isSuccessful());
    }

    #[Test]
    public function client_response_is_redirect_for_3xx_codes() : void
    {
        $this->assertTrue((new ClientResponse(statusCode: 301))->isRedirect());
        $this->assertTrue((new ClientResponse(statusCode: 302))->isRedirect());
        $this->assertTrue((new ClientResponse(statusCode: 304))->isRedirect());
        $this->assertTrue((new ClientResponse(statusCode: 399))->isRedirect());
        $this->assertFalse((new ClientResponse(statusCode: 299))->isRedirect());
        $this->assertFalse((new ClientResponse(statusCode: 400))->isRedirect());
    }

    #[Test]
    public function client_response_is_client_error_for_4xx_codes() : void
    {
        $this->assertTrue((new ClientResponse(statusCode: 400))->isClientError());
        $this->assertTrue((new ClientResponse(statusCode: 404))->isClientError());
        $this->assertTrue((new ClientResponse(statusCode: 422))->isClientError());
        $this->assertTrue((new ClientResponse(statusCode: 499))->isClientError());
        $this->assertFalse((new ClientResponse(statusCode: 399))->isClientError());
        $this->assertFalse((new ClientResponse(statusCode: 500))->isClientError());
    }

    #[Test]
    public function client_response_is_server_error_for_5xx_codes() : void
    {
        $this->assertTrue((new ClientResponse(statusCode: 500))->isServerError());
        $this->assertTrue((new ClientResponse(statusCode: 502))->isServerError());
        $this->assertTrue((new ClientResponse(statusCode: 503))->isServerError());
        $this->assertTrue((new ClientResponse(statusCode: 599))->isServerError());
        $this->assertFalse((new ClientResponse(statusCode: 499))->isServerError());
        $this->assertFalse((new ClientResponse(statusCode: 600))->isServerError());
    }

    #[Test]
    public function client_response_has_error_for_4xx_and_5xx() : void
    {
        $this->assertTrue((new ClientResponse(statusCode: 404))->hasError());
        $this->assertTrue((new ClientResponse(statusCode: 500))->hasError());
        $this->assertFalse((new ClientResponse(statusCode: 200))->hasError());
        $this->assertFalse((new ClientResponse(statusCode: 301))->hasError());
    }

    #[Test]
    public function client_response_get_content_type_strips_parameters() : void
    {
        $r1 = new ClientResponse(headers: ['Content-Type' => ['application/json; charset=utf-8']]);
        $r2 = new ClientResponse(headers: ['Content-Type' => ['text/html']]);
        $r3 = new ClientResponse;

        $this->assertEquals('application/json', $r1->getContentType());
        $this->assertEquals('text/html', $r2->getContentType());
        $this->assertNull($r3->getContentType());
    }

    #[Test]
    public function client_response_get_header_line_returns_comma_separated() : void
    {
        $response = new ClientResponse(headers: [
                                                    'Content-Type' => ['application/json'],
                                                    'X-Custom'     => ['value1', 'value2', 'value3'],
                                                ]);

        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('value1, value2, value3', $response->getHeaderLine('X-Custom'));
        $this->assertEquals('', $response->getHeaderLine('Non-Existent'));
    }

    #[Test]
    public function client_response_get_header_returns_array() : void
    {
        $response = new ClientResponse(headers: ['X-Multi' => ['a', 'b', 'c']]);
        $this->assertEquals(['a', 'b', 'c'], $response->getHeader('X-Multi'));
        $this->assertEquals([], $response->getHeader('Non-Existent'));
    }

    #[Test]
    public function client_response_has_header_checks_existence() : void
    {
        $response = new ClientResponse(headers: ['Content-Type' => ['application/json']]);
        $this->assertTrue($response->hasHeader('Content-Type'));
        $this->assertFalse($response->hasHeader('Non-Existent'));
    }

    #[Test]
    public function client_response_json_decode_as_array() : void
    {
        $response = new ClientResponse(body: '{"name":"John","age":30}');
        $data = $response->json(assoc: true);
        $this->assertIsArray($data);
        $this->assertEquals('John', $data['name']);
        $this->assertEquals(30, $data['age']);
    }

    #[Test]
    public function client_response_json_decode_as_object() : void
    {
        $response = new ClientResponse(body: '{"name":"John","age":30}');
        $data = $response->json(assoc: false);
        $this->assertIsObject($data);
        $this->assertEquals('John', $data->name);
        $this->assertEquals(30, $data->age);
    }

    #[Test]
    public function client_response_json_decode_invalid_json_throws() : void
    {
        $response = new ClientResponse(body: 'not valid json');
        $this->expectException(JsonException::class);
        $response->json();
    }

    #[Test]
    public function client_response_get_effective_url_returns_url_or_empty() : void
    {
        $r1 = new ClientResponse(effectiveUrl: 'https://example.com/final');
        $r2 = new ClientResponse;
        $this->assertEquals('https://example.com/final', $r1->getEffectiveUrl());
        $this->assertEquals('', $r2->getEffectiveUrl());
    }

    // ==========================================
    // 4. ResponseDecoder Tests
    // ==========================================

    #[Test]
    public function response_decoder_decode_json_by_content_type() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '{"status":"ok","count":5}', headers: ['Content-Type' => ['application/json']]);
        $result  = $decoder->decode($response);
        $this->assertIsArray($result);
        $this->assertEquals('ok', $result['status']);
        $this->assertEquals(5, $result['count']);
    }

    #[Test]
    public function response_decoder_decode_json_with_charset() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '{"data":"test"}', headers: ['Content-Type' => ['application/json; charset=utf-8']]);
        $result  = $decoder->decode($response);
        $this->assertEquals(['data' => 'test'], $result);
    }

    #[Test]
    public function response_decoder_decode_xml_by_content_type() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '<root><item>test</item></root>', headers: ['Content-Type' => ['application/xml']]);
        $result  = $decoder->decode($response);
        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertEquals('test', (string) $result->item);
    }

    #[Test]
    public function response_decoder_decode_text_by_content_type() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: 'Plain text response', headers: ['Content-Type' => ['text/plain']]);
        $result  = $decoder->decode($response);
        $this->assertEquals('Plain text response', $result);
    }

    #[Test]
    public function response_decoder_decode_html_as_text() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '<html><body>Hello</body></html>', headers: ['Content-Type' => ['text/html']]);
        $result  = $decoder->decode($response);
        $this->assertEquals('<html><body>Hello</body></html>', $result);
    }

    #[Test]
    public function response_decoder_decode_unknown_content_type_as_text() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: 'Binary-like content', headers: ['Content-Type' => ['application/octet-stream']]);
        $result  = $decoder->decode($response);
        $this->assertEquals('Binary-like content', $result);
    }

    #[Test]
    public function response_decoder_decode_no_content_type_as_text() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: 'No content type specified');
        $result  = $decoder->decode($response);
        $this->assertEquals('No content type specified', $result);
    }

    #[Test]
    public function response_decoder_force_json_format() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '{"forced":true}');
        $result  = $decoder->decode($response, format: 'json');
        $this->assertEquals(['forced' => true], $result);
    }

    #[Test]
    public function response_decoder_force_xml_format() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '<root><value>123</value></root>');
        $result  = $decoder->decode($response, format: 'xml');
        $this->assertInstanceOf(SimpleXMLElement::class, $result);
        $this->assertEquals('123', (string) $result->value);
    }

    #[Test]
    public function response_decoder_force_text_format() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '{"json":"string"}', headers: ['Content-Type' => ['application/json']]);
        $result  = $decoder->decode($response, format: 'text');
        $this->assertEquals('{"json":"string"}', $result);
    }

    #[Test]
    public function response_decoder_unsupported_format_throws() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: 'test', statusCode: 200);
        $this->expectException(InvalidHttpResponse::class);
        $this->expectExceptionMessage('Unsupported response format: yaml');
        $decoder->decode($response, format: 'yaml');
    }

    #[Test]
    public function response_decoder_decode_json_empty_body_throws() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '');
        $this->expectException(InvalidHttpResponse::class);
        $this->expectExceptionMessage('Empty response body cannot be decoded as JSON');
        $decoder->decodeJson($response);
    }

    #[Test]
    public function response_decoder_decode_json_invalid_json_throws() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '{invalid json}', statusCode: 200);
        $this->expectException(InvalidHttpResponse::class);
        $this->expectExceptionMessage('Failed to decode JSON response');
        $decoder->decodeJson($response);
    }

    #[Test]
    public function response_decoder_decode_json_returns_array() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '{"key":"value"}');
        $result  = $decoder->decodeJson($response, assoc: true);
        $this->assertIsArray($result);
        $this->assertEquals(['key' => 'value'], $result);
    }

    #[Test]
    public function response_decoder_decode_json_returns_object() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '{"key":"value"}');
        $result  = $decoder->decodeJson($response, assoc: false);
        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
    }

    #[Test]
    public function response_decoder_decode_xml_empty_body_throws() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '');
        $this->expectException(InvalidHttpResponse::class);
        $this->expectExceptionMessage('Empty response body cannot be decoded as XML');
        $decoder->decodeXml($response);
    }

    #[Test]
    public function response_decoder_decode_xml_invalid_xml_throws() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '<invalid><xml>', statusCode: 200);
        $this->expectException(InvalidHttpResponse::class);
        $this->expectExceptionMessage('Failed to decode XML response');
        $decoder->decodeXml($response);
    }

    #[Test]
    public function response_decoder_decode_text_returns_body() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: 'Hello, World!');
        $result  = $decoder->decodeText($response);
        $this->assertEquals('Hello, World!', $result);
    }

    #[Test]
    public function response_decoder_decode_text_empty_body() : void
    {
        $decoder = new ResponseDecoder;
        $response = new ClientResponse(body: '');
        $result  = $decoder->decodeText($response);
        $this->assertEquals('', $result);
    }

    // ==========================================
    // 5. RetryPolicy Tests
    // ==========================================

    #[Test]
    public function retry_policy_default_values() : void
    {
        $policy = new RetryPolicy;
        $this->assertEquals(3, $policy->attempts);
        $this->assertEquals(1000, $policy->baseDelayMs);
        $this->assertEquals(2.0, $policy->backoffMultiplier);
        $this->assertEquals(30_000, $policy->maxDelayMs);
        $this->assertFalse($policy->jitter);
        $this->assertEquals([500, 502, 503, 504], $policy->retryOnStatus);
        $this->assertTrue($policy->retryOnTimeout);
        $this->assertTrue($policy->retryOnConnectionError);
    }

    #[Test]
    public function retry_policy_exponential_factory() : void
    {
        $policy = RetryPolicy::exponential(attempts: 4, baseDelayMs: 500, multiplier: 3.0);
        $this->assertEquals(4, $policy->attempts);
        $this->assertEquals(500, $policy->baseDelayMs);
        $this->assertEquals(3.0, $policy->backoffMultiplier);
        $this->assertFalse($policy->jitter);
    }

    #[Test]
    public function retry_policy_exponential_delays() : void
    {
        $policy = RetryPolicy::exponential(attempts: 4, baseDelayMs: 100, multiplier: 2.0);
        $this->assertEquals(100, $policy->delayForAttempt(1));
        $this->assertEquals(200, $policy->delayForAttempt(2));
        $this->assertEquals(400, $policy->delayForAttempt(3));
        $this->assertEquals(800, $policy->delayForAttempt(4));
    }

    #[Test]
    public function retry_policy_fixed_factory() : void
    {
        $policy = RetryPolicy::fixed(attempts: 5, delayMs: 2000);
        $this->assertEquals(5, $policy->attempts);
        $this->assertEquals(2000, $policy->baseDelayMs);
        $this->assertEquals(1.0, $policy->backoffMultiplier);
    }

    #[Test]
    public function retry_policy_fixed_delays_are_constant() : void
    {
        $policy = RetryPolicy::fixed(attempts: 3, delayMs: 1500);
        $this->assertEquals(1500, $policy->delayForAttempt(1));
        $this->assertEquals(1500, $policy->delayForAttempt(2));
        $this->assertEquals(1500, $policy->delayForAttempt(3));
    }

    #[Test]
    public function retry_policy_linear_factory() : void
    {
        $policy = RetryPolicy::linear(attempts: 3, baseDelayMs: 1000);
        $this->assertEquals(3, $policy->attempts);
        $this->assertEquals(1000, $policy->baseDelayMs);
        $this->assertEquals(1.0, $policy->backoffMultiplier);
    }

    #[Test]
    public function retry_policy_none_factory() : void
    {
        $policy = RetryPolicy::none();
        $this->assertEquals(0, $policy->attempts);
    }

    #[Test]
    public function retry_policy_delay_capped_at_max() : void
    {
        $policy = new RetryPolicy(attempts: 5, baseDelayMs: 1000, backoffMultiplier: 10.0, maxDelayMs: 5000);
        $delay3 = $policy->delayForAttempt(3);
        $this->assertEquals(5000, $delay3);
    }

    #[Test]
    public function retry_policy_jitter_applied() : void
    {
        $policy = new RetryPolicy(attempts: 3, baseDelayMs: 1000, backoffMultiplier: 1.0, jitter: true);
        $delays = [];
        for ($i = 0; $i < 10; $i++) {
            $delays[] = $policy->delayForAttempt(1);
        }
        foreach ($delays as $delay) {
            $this->assertGreaterThanOrEqual(750, $delay);
            $this->assertLessThanOrEqual(1250, $delay);
        }
    }

    #[Test]
    public function retry_policy_jitter_ensures_positive_delay() : void
    {
        $policy = new RetryPolicy(attempts: 3, baseDelayMs: 1, backoffMultiplier: 1.0, jitter: true);
        for ($i = 0; $i < 20; $i++) {
            $delay = $policy->delayForAttempt(1);
            $this->assertGreaterThanOrEqual(1, $delay);
        }
    }

    #[Test]
    public function retry_policy_delay_for_attempt_out_of_range_returns_zero() : void
    {
        $policy = RetryPolicy::exponential(attempts: 3, baseDelayMs: 1000);
        $this->assertEquals(0, $policy->delayForAttempt(0));
        $this->assertEquals(0, $policy->delayForAttempt(4));
        $this->assertEquals(0, $policy->delayForAttempt(10));
    }

    #[Test]
    public function retry_policy_should_retry_status() : void
    {
        $policy = new RetryPolicy;
        $this->assertTrue($policy->shouldRetryStatus(500));
        $this->assertTrue($policy->shouldRetryStatus(502));
        $this->assertTrue($policy->shouldRetryStatus(503));
        $this->assertTrue($policy->shouldRetryStatus(504));
        $this->assertFalse($policy->shouldRetryStatus(200));
        $this->assertFalse($policy->shouldRetryStatus(400));
        $this->assertFalse($policy->shouldRetryStatus(404));
    }

    #[Test]
    public function retry_policy_custom_retry_on_status() : void
    {
        $policy = new RetryPolicy(retryOnStatus: [429, 500]);
        $this->assertTrue($policy->shouldRetryStatus(429));
        $this->assertTrue($policy->shouldRetryStatus(500));
        $this->assertFalse($policy->shouldRetryStatus(502));
        $this->assertFalse($policy->shouldRetryStatus(503));
    }

    #[Test]
    public function retry_policy_should_retry_timeout() : void
    {
        $this->assertTrue((new RetryPolicy(retryOnTimeout: true))->shouldRetryTimeout());
        $this->assertFalse((new RetryPolicy(retryOnTimeout: false))->shouldRetryTimeout());
    }

    #[Test]
    public function retry_policy_should_retry_connection_error() : void
    {
        $this->assertTrue((new RetryPolicy(retryOnConnectionError: true))->shouldRetryConnectionError());
        $this->assertFalse((new RetryPolicy(retryOnConnectionError: false))->shouldRetryConnectionError());
    }

    #[Test]
    public function retry_policy_has_remaining_attempts() : void
    {
        $policy = RetryPolicy::exponential(attempts: 3);
        $this->assertTrue($policy->hasRemainingAttempts(1));
        $this->assertTrue($policy->hasRemainingAttempts(2));
        $this->assertTrue($policy->hasRemainingAttempts(3));
        $this->assertFalse($policy->hasRemainingAttempts(4));
        // Note: hasRemainingAttempts(0) returns true because 0 <= 3
        $this->assertTrue($policy->hasRemainingAttempts(0));
    }

    #[Test]
    public function retry_policy_get_total_max_delay_exponential() : void
    {
        $policy = RetryPolicy::exponential(attempts: 3, baseDelayMs: 100, multiplier: 2.0);
        $this->assertEquals(700, $policy->getTotalMaxDelay());
    }

    #[Test]
    public function retry_policy_get_total_max_delay_fixed() : void
    {
        $policy = RetryPolicy::fixed(attempts: 3, delayMs: 500);
        $this->assertEquals(1500, $policy->getTotalMaxDelay());
    }

    #[Test]
    public function retry_policy_get_total_max_delay_none() : void
    {
        $policy = RetryPolicy::none();
        $this->assertEquals(0, $policy->getTotalMaxDelay());
    }

    // ==========================================
    // 6. TimeoutPolicy Tests
    // ==========================================

    #[Test]
    public function timeout_policy_default_values() : void
    {
        $policy = new TimeoutPolicy;
        $this->assertEquals(5000, $policy->connectTimeoutMs);
        $this->assertEquals(30000, $policy->transferTimeoutMs);
        $this->assertEquals(30000, $policy->timeoutMs);
        $this->assertTrue($policy->enforce);
    }

    #[Test]
    public function timeout_policy_strict_factory() : void
    {
        $policy = TimeoutPolicy::strict(5000);
        $this->assertEquals(5000, $policy->connectTimeoutMs);
        $this->assertEquals(5000, $policy->transferTimeoutMs);
        $this->assertEquals(5000, $policy->timeoutMs);
        $this->assertTrue($policy->enforce);
    }

    #[Test]
    public function timeout_policy_strict_custom_value() : void
    {
        $policy = TimeoutPolicy::strict(10000);
        $this->assertEquals(10000, $policy->connectTimeoutMs);
        $this->assertEquals(10000, $policy->transferTimeoutMs);
        $this->assertEquals(10000, $policy->timeoutMs);
    }

    #[Test]
    public function timeout_policy_relaxed_factory() : void
    {
        $policy = TimeoutPolicy::relaxed();
        $this->assertEquals(60_000, $policy->connectTimeoutMs);
        $this->assertEquals(60_000, $policy->transferTimeoutMs);
        $this->assertEquals(60_000, $policy->timeoutMs);
    }

    #[Test]
    public function timeout_policy_relaxed_custom_value() : void
    {
        $policy = TimeoutPolicy::relaxed(120_000);
        $this->assertEquals(120_000, $policy->connectTimeoutMs);
        $this->assertEquals(120_000, $policy->transferTimeoutMs);
        $this->assertEquals(120_000, $policy->timeoutMs);
    }

    #[Test]
    public function timeout_policy_fast_factory() : void
    {
        $policy = TimeoutPolicy::fast();
        $this->assertEquals(2000, $policy->connectTimeoutMs);
        $this->assertEquals(5000, $policy->transferTimeoutMs);
        $this->assertEquals(5000, $policy->timeoutMs);
    }

    #[Test]
    public function timeout_policy_streaming_factory() : void
    {
        $policy = TimeoutPolicy::streaming();
        $this->assertEquals(10_000, $policy->connectTimeoutMs);
        $this->assertEquals(300_000, $policy->transferTimeoutMs);
        $this->assertEquals(300_000, $policy->timeoutMs);
    }

    #[Test]
    public function timeout_policy_none_factory() : void
    {
        $policy = TimeoutPolicy::none();
        $this->assertEquals(0, $policy->connectTimeoutMs);
        $this->assertEquals(0, $policy->transferTimeoutMs);
        $this->assertEquals(0, $policy->timeoutMs);
        $this->assertFalse($policy->enforce);
    }

    #[Test]
    public function timeout_policy_min_timeout() : void
    {
        $policy = new TimeoutPolicy(connectTimeoutMs: 3000, transferTimeoutMs: 10000, timeoutMs: 30000);
        $this->assertEquals(3000, $policy->minTimeout());
    }

    #[Test]
    public function timeout_policy_max_timeout() : void
    {
        $policy = new TimeoutPolicy(connectTimeoutMs: 3000, transferTimeoutMs: 10000, timeoutMs: 30000);
        $this->assertEquals(30000, $policy->maxTimeout());
    }

    #[Test]
    public function timeout_policy_max_timeout_with_connect_as_max() : void
    {
        $policy = new TimeoutPolicy(connectTimeoutMs: 50000, transferTimeoutMs: 10000, timeoutMs: 30000);
        $this->assertEquals(50000, $policy->maxTimeout());
    }

    #[Test]
    public function timeout_policy_custom_constructor_values() : void
    {
        $policy = new TimeoutPolicy(connectTimeoutMs: 2000, transferTimeoutMs: 15000, timeoutMs: 20000, enforce: false);
        $this->assertEquals(2000, $policy->connectTimeoutMs);
        $this->assertEquals(15000, $policy->transferTimeoutMs);
        $this->assertEquals(20000, $policy->timeoutMs);
        $this->assertFalse($policy->enforce);
    }

    // ==========================================
    // 7. FakeHttpClient Tests
    // ==========================================

    #[Test]
    public function fake_http_client_get_request() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/users' => RecordedHttpResponse::json('https://api.example.com/users', ['id' => 1, 'name' => 'John']),
                                                ]);
        $response = $client->get('https://api.example.com/users');
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals(['id' => 1, 'name' => 'John'], $response->json());
    }

    #[Test]
    public function fake_http_client_post_request() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/users' => RecordedHttpResponse::json('https://api.example.com/users', ['id' => 1], 201),
                                                ]);
        $response = $client->post('https://api.example.com/users', '{"name":"John"}');
        $this->assertEquals(201, $response->statusCode);
        $this->assertEquals(['id' => 1], $response->json());
    }

    #[Test]
    public function fake_http_client_put_request() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/users/1' => RecordedHttpResponse::json('https://api.example.com/users/1', ['id' => 1, 'updated' => true]),
                                                ]);
        $response = $client->put('https://api.example.com/users/1', '{"name":"Updated"}');
        $this->assertEquals(200, $response->statusCode);
        $this->assertTrue($response->json()['updated']);
    }

    #[Test]
    public function fake_http_client_delete_request() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/users/1' => RecordedHttpResponse::ok('https://api.example.com/users/1', 'deleted'),
                                                ]);
        $response = $client->delete('https://api.example.com/users/1');
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('deleted', $response->body);
    }

    #[Test]
    public function fake_http_client_head_request() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/health' => RecordedHttpResponse::ok('https://api.example.com/health'),
                                                ]);
        $response = $client->head('https://api.example.com/health');
        $this->assertEquals(200, $response->statusCode);
    }

    #[Test]
    public function fake_http_client_options_request() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com' => RecordedHttpResponse::ok('https://api.example.com'),
                                                ]);
        $response = $client->options('https://api.example.com');
        $this->assertEquals(200, $response->statusCode);
    }

    #[Test]
    public function fake_http_client_send_records_request() : void
    {
        $opts   = new RequestOptions;
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/test' => RecordedHttpResponse::ok('https://api.example.com/test'),
                                                ]);
        $request = new OutboundRequest(method: 'GET', url: 'https://api.example.com/test', options: $opts);
        $client->send($request);
        $this->assertEquals(1, $client->getRequestCount());
        $this->assertEquals('GET', $client->getLastRequest()->method);
        $this->assertEquals('https://api.example.com/test', $client->getLastRequest()->url);
    }

    #[Test]
    public function fake_http_client_no_matching_response_throws() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/existing' => RecordedHttpResponse::ok('https://api.example.com/existing'),
                                                ]);
        $this->expectException(HttpRequestFailed::class);
        $this->expectExceptionMessage('No recorded response found for GET https://api.example.com/nonexistent');
        $client->get('https://api.example.com/nonexistent');
    }

    #[Test]
    public function fake_http_client_get_recorded_requests() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/a' => RecordedHttpResponse::ok('https://api.example.com/a'),
                                                    'https://api.example.com/b' => RecordedHttpResponse::ok('https://api.example.com/b'),
                                                ]);
        $client->get('https://api.example.com/a');
        $client->get('https://api.example.com/b');
        $requests = $client->getRecordedRequests();
        $this->assertCount(2, $requests);
        $this->assertEquals('https://api.example.com/a', $requests[0]->url);
        $this->assertEquals('https://api.example.com/b', $requests[1]->url);
    }

    #[Test]
    public function fake_http_client_clear_requests() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/test' => RecordedHttpResponse::ok('https://api.example.com/test'),
                                                ]);
        $client->get('https://api.example.com/test');
        $this->assertEquals(1, $client->getRequestCount());
        $client->clearRequests();
        $this->assertEquals(0, $client->getRequestCount());
        $this->assertNull($client->getLastRequest());
    }

    #[Test]
    public function fake_http_client_base_url_resolution() : void
    {
        $client = FakeHttpClient::fromResponses(
            responses: ['https://api.example.com/users' => RecordedHttpResponse::ok('https://api.example.com/users')],
            baseUrl  : 'https://api.example.com',
        );
        $response = $client->get('/users');
        $this->assertEquals(200, $response->statusCode);
    }

    #[Test]
    public function fake_http_client_full_url_ignores_base() : void
    {
        $client = FakeHttpClient::fromResponses(
            responses: ['https://other-api.com/data' => RecordedHttpResponse::ok('https://other-api.com/data')],
            baseUrl  : 'https://api.example.com',
        );
        $response = $client->get('https://other-api.com/data');
        $this->assertEquals(200, $response->statusCode);
    }

    #[Test]
    public function fake_http_client_get_default_timeout() : void
    {
        $client = FakeHttpClient::fromResponses([]);
        $this->assertEquals(30000, $client->getDefaultTimeout());
    }

    #[Test]
    public function fake_http_client_get_base_url() : void
    {
        $client = FakeHttpClient::fromResponses([], baseUrl: 'https://api.example.com');
        $this->assertEquals('https://api.example.com', $client->getBaseUrl());
    }

    #[Test]
    public function fake_http_client_get_base_url_null_when_not_set() : void
    {
        $client = FakeHttpClient::fromResponses([]);
        $this->assertNull($client->getBaseUrl());
    }

    #[Test]
    public function fake_http_client_error_response() : void
    {
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/fail' => RecordedHttpResponse::error('https://api.example.com/fail', 500, 'Server Error'),
                                                ]);
        $response = $client->get('https://api.example.com/fail');
        $this->assertEquals(500, $response->statusCode);
        $this->assertEquals('Server Error', $response->body);
    }

    #[Test]
    public function fake_http_client_exception_response() : void
    {
        $exception = new RuntimeException('Simulated failure');
        $client = FakeHttpClient::fromResponses([
                                                    'https://api.example.com/throw' => RecordedHttpResponse::throws('https://api.example.com/throw', $exception),
                                                ]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Simulated failure');
        $client->get('https://api.example.com/throw');
    }

    #[Test]
    public function fake_http_client_builder_respond_with_json() : void
    {
        $client = FakeHttpClient::create()
            ->whenGet('https://api.example.com/users')
            ->respondWithJson(['id' => 1, 'name' => 'Jane'])
            ->build();
        $response = $client->get('https://api.example.com/users');
        $this->assertEquals(200, $response->statusCode);
        $data = $response->json();
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Jane', $data['name']);
    }

    #[Test]
    public function fake_http_client_builder_respond_with_status() : void
    {
        $client = FakeHttpClient::create()
            ->whenPost('https://api.example.com/users')
            ->respondWithStatus(201, 'Created')
            ->build();
        $response = $client->post('https://api.example.com/users', '{"name":"John"}');
        $this->assertEquals(201, $response->statusCode);
        $this->assertEquals('Created', $response->body);
    }

    #[Test]
    public function fake_http_client_builder_respond_with_exception() : void
    {
        $client = FakeHttpClient::create()
            ->whenGet('https://api.example.com/fail')
            ->respondWithException(new RuntimeException('Connection failed'))
            ->build();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Connection failed');
        $client->get('https://api.example.com/fail');
    }

    #[Test]
    public function fake_http_client_builder_multiple_responses() : void
    {
        $client = FakeHttpClient::create()
            ->whenGet('https://api.example.com/users')
            ->respondWithJson(['users' => []])
            ->whenPost('https://api.example.com/users/create')
            ->respondWithStatus(201)
            ->build();
        $getResponse = $client->get('https://api.example.com/users');
        $postResponse = $client->post('https://api.example.com/users/create', '{}');
        $this->assertEquals(200, $getResponse->statusCode);
        $this->assertEquals(201, $postResponse->statusCode);
    }

    #[Test]
    public function fake_http_client_builder_with_responses() : void
    {
        $client = FakeHttpClient::create()
            ->withResponses(['key1' => RecordedHttpResponse::ok('https://api.example.com/test')])
            ->build();
        $response = $client->get('https://api.example.com/test');
        $this->assertEquals(200, $response->statusCode);
    }

    #[Test]
    public function fake_http_client_builder_respond_with_custom() : void
    {
        $customResponse = new RecordedHttpResponse(
            urlPattern: 'https://api.example.com/custom',
            method    : 'GET',
            statusCode: 202,
            body      : 'Accepted',
        );
        $client = FakeHttpClient::create()
            ->whenGet('https://api.example.com/custom')
            ->respondWith($customResponse)
            ->build();
        $response = $client->get('https://api.example.com/custom');
        $this->assertEquals(202, $response->statusCode);
        $this->assertEquals('Accepted', $response->body);
    }

    // ==========================================
    // 8. RecordedHttpResponse Tests
    // ==========================================

    #[Test]
    public function recorded_http_response_ok_factory() : void
    {
        $response = RecordedHttpResponse::ok('https://example.com', 'OK body', ['X-Custom' => 'value']);
        $this->assertEquals('https://example.com', $response->urlPattern);
        $this->assertEquals('*', $response->method);
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('OK body', $response->body);
        $this->assertEquals(['X-Custom' => 'value'], $response->headers);
        $this->assertFalse($response->useRegex);
    }

    #[Test]
    public function recorded_http_response_json_factory() : void
    {
        $response = RecordedHttpResponse::json('https://example.com', ['key' => 'value'], 201);
        $this->assertEquals(201, $response->statusCode);
        $this->assertEquals('{"key":"value"}', $response->body);
        $this->assertEquals(['Content-Type' => 'application/json'], $response->headers);
    }

    #[Test]
    public function recorded_http_response_error_factory() : void
    {
        $response = RecordedHttpResponse::error('https://example.com', 503, 'Service Unavailable');
        $this->assertEquals(503, $response->statusCode);
        $this->assertEquals('Service Unavailable', $response->body);
    }

    #[Test]
    public function recorded_http_response_throws_factory() : void
    {
        $exception = new RuntimeException('Test exception');
        $response = RecordedHttpResponse::throws('https://example.com', $exception);
        $this->assertSame($exception, $response->exception);
        $this->assertEquals('*', $response->method);
    }

    #[Test]
    public function recorded_http_response_delayed_factory() : void
    {
        $response = RecordedHttpResponse::delayed('https://example.com', 500.5);
        $this->assertEquals(500.5, $response->delayMs);
        $this->assertEquals('*', $response->method);
    }

    #[Test]
    public function recorded_http_response_matches_url_and_method() : void
    {
        $response = new RecordedHttpResponse(urlPattern: 'https://example.com/api', method: 'GET');
        $this->assertTrue($response->matches('https://example.com/api', 'GET'));
        $this->assertTrue($response->matches('https://example.com/api', 'get'));
        $this->assertFalse($response->matches('https://example.com/other', 'GET'));
        $this->assertFalse($response->matches('https://example.com/api', 'POST'));
    }

    #[Test]
    public function recorded_http_response_matches_any_method() : void
    {
        $response = new RecordedHttpResponse(urlPattern: 'https://example.com/api', method: '*');
        $this->assertTrue($response->matches('https://example.com/api', 'GET'));
        $this->assertTrue($response->matches('https://example.com/api', 'POST'));
        $this->assertTrue($response->matches('https://example.com/api', 'DELETE'));
        $this->assertFalse($response->matches('https://example.com/other', 'GET'));
    }

    #[Test]
    public function recorded_http_response_matches_regex_url() : void
    {
        $response = new RecordedHttpResponse(
            urlPattern: '/^https:\/\/example\.com\/api\/.+/',
            method    : '*',
            useRegex  : true,
        );
        $this->assertTrue($response->matches('https://example.com/api/users', 'GET'));
        $this->assertTrue($response->matches('https://example.com/api/posts/123', 'GET'));
        $this->assertFalse($response->matches('https://example.com/api', 'GET'));
        $this->assertFalse($response->matches('https://other.com/api/users', 'GET'));
    }

    #[Test]
    public function recorded_http_response_custom_constructor() : void
    {
        $response = new RecordedHttpResponse(
            urlPattern: '/pattern/',
            method    : 'POST',
            statusCode: 400,
            headers   : ['Content-Type' => 'application/json'],
            body      : '{"error":"bad request"}',
            delayMs   : 100.0,
            useRegex  : true,
        );
        $this->assertEquals('/pattern/', $response->urlPattern);
        $this->assertEquals('POST', $response->method);
        $this->assertEquals(400, $response->statusCode);
        $this->assertEquals(['Content-Type' => 'application/json'], $response->headers);
        $this->assertEquals('{"error":"bad request"}', $response->body);
        $this->assertEquals(100.0, $response->delayMs);
        $this->assertTrue($response->useRegex);
    }

    // ==========================================
    // 9. CurlTransport Tests
    // ==========================================

    #[Test]
    public function curl_transport_creation() : void
    {
        $transport = new CurlTransport;
        $this->assertInstanceOf(CurlTransport::class, $transport);
    }

    #[Test]
    public function curl_transport_implements_interface() : void
    {
        $transport = new CurlTransport;
        $this->assertInstanceOf(
            HttpTransportInterface::class,
            $transport,
        );
    }

    #[Test]
    public function curl_transport_build_psr7_request() : void
    {
        $opts      = new RequestOptions;
        $transport = new CurlTransport;
        $request   = new OutboundRequest(
            method : 'POST',
            url    : 'https://example.com/api',
            body   : '{"test":"data"}',
            headers: ['Content-Type' => 'application/json'],
            options: $opts,
        );
        $psr7Request = $transport->buildPsr7Request($request);
        $this->assertEquals('POST', $psr7Request->getMethod());
        $this->assertEquals('https://example.com/api', (string) $psr7Request->getUri());
        $this->assertEquals('application/json', $psr7Request->getHeaderLine('Content-Type'));
        $this->assertEquals('{"test":"data"}', (string) $psr7Request->getBody());
    }

    #[Test]
    public function curl_transport_build_psr7_request_with_null_body() : void
    {
        $opts      = new RequestOptions;
        $transport = new CurlTransport;
        $request   = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);
        $psr7Request = $transport->buildPsr7Request($request);
        $this->assertEquals('GET', $psr7Request->getMethod());
        $this->assertEquals('', (string) $psr7Request->getBody());
    }

    #[Test]
    public function curl_transport_build_psr7_request_with_array_body() : void
    {
        $opts      = new RequestOptions;
        $transport = new CurlTransport;
        $request   = new OutboundRequest(method: 'POST', url: 'https://example.com', body: ['key' => 'value'], options: $opts);
        $psr7Request = $transport->buildPsr7Request($request);
        $this->assertEquals('{"key":"value"}', (string) $psr7Request->getBody());
    }

    // ==========================================
    // 10. ClientMiddlewarePipeline Tests
    // ==========================================

    #[Test]
    public function middleware_pipeline_empty_creation() : void
    {
        $pipeline = new ClientMiddlewarePipeline;
        $this->assertEquals([], $pipeline->middlewares);
        $this->assertEquals(0, $pipeline->count());
        $this->assertTrue($pipeline->isEmpty());
    }

    #[Test]
    public function middleware_pipeline_with_middlewares() : void
    {
        $middleware1 = $this->createPassThroughMiddleware();
        $middleware2 = $this->createPassThroughMiddleware();
        $pipeline = new ClientMiddlewarePipeline([$middleware1, $middleware2]);
        $this->assertCount(2, $pipeline->middlewares);
        $this->assertEquals(2, $pipeline->count());
        $this->assertFalse($pipeline->isEmpty());
    }

    private function createPassThroughMiddleware() : ClientMiddlewareInterface
    {
        return new class implements ClientMiddlewareInterface {
            public function handle(OutboundRequest $request, Closure $handler) : ClientResponse
            {
                return $handler($request);
            }
        };
    }

    #[Test]
    public function middleware_pipeline_with_adds_immutable() : void
    {
        $middleware1 = $this->createPassThroughMiddleware();
        $middleware2 = $this->createPassThroughMiddleware();
        $original = new ClientMiddlewarePipeline([$middleware1]);
        $modified = $original->with($middleware2);
        $this->assertNotSame($original, $modified);
        $this->assertCount(1, $original->middlewares);
        $this->assertCount(2, $modified->middlewares);
    }

    #[Test]
    public function middleware_pipeline_resolve_without_middlewares() : void
    {
        $pipeline = new ClientMiddlewarePipeline;
        $opts     = new RequestOptions;
        $handler  = $pipeline->resolve(static fn (OutboundRequest $request) : ClientResponse => new ClientResponse(statusCode: 200, body: 'final'));
        $request  = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);
        $response = $handler($request);
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('final', $response->body);
    }

    #[Test]
    public function middleware_pipeline_execute_without_middlewares() : void
    {
        $pipeline = new ClientMiddlewarePipeline;
        $opts     = new RequestOptions;
        $request  = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);
        $response = $pipeline->execute($request, static fn (OutboundRequest $req) : ClientResponse => new ClientResponse(statusCode: 200, body: 'direct'));
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('direct', $response->body);
    }

    #[Test]
    public function middleware_pipeline_single_middleware_execution() : void
    {
        $middleware = $this->createPassThroughMiddleware();
        $pipeline = new ClientMiddlewarePipeline([$middleware]);
        $opts = new RequestOptions;
        $request = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);
        $response = $pipeline->execute($request, static fn (OutboundRequest $req) : ClientResponse => new ClientResponse(statusCode: 200, body: 'final-handler'));
        $this->assertEquals(200, $response->statusCode);
        $this->assertEquals('final-handler', $response->body);
    }

    #[Test]
    public function middleware_pipeline_multiple_middlewares_execution_order() : void
    {
        $order   = [];
        $middleware1 = $this->createTrackingMiddleware('m1', $order);
        $middleware2 = $this->createTrackingMiddleware('m2', $order);
        $middleware3 = $this->createTrackingMiddleware('m3', $order);

        $pipeline = new ClientMiddlewarePipeline([$middleware1, $middleware2, $middleware3]);
        $opts    = new RequestOptions;
        $request = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);

        $pipeline->execute($request, static function (OutboundRequest $req) use (&$order) : ClientResponse {
            $order[] = 'handler';

            return new ClientResponse(statusCode: 200);
        });

        $this->assertEquals(
            ['m1-request', 'm2-request', 'm3-request', 'handler', 'm3-response', 'm2-response', 'm1-response'],
            $order,
        );
    }

    /**
     * @param list<string> $order
     */
    private function createTrackingMiddleware(string $name, array &$order) : ClientMiddlewareInterface
    {
        return new TrackingMiddleware($name, $order);
    }

    #[Test]
    public function middleware_pipeline_middleware_can_modify_request() : void
    {
        $modifiedRequest = null;
        $headerMiddleware = $this->createHeaderAddingMiddleware();

        $pipeline = new ClientMiddlewarePipeline([$headerMiddleware]);
        $opts            = new RequestOptions;
        $request         = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);

        $pipeline->execute($request, static function (OutboundRequest $req) use (&$modifiedRequest) : ClientResponse {
            $modifiedRequest = $req;

            return new ClientResponse(statusCode: 200);
        });

        $this->assertNotNull($modifiedRequest);
        $this->assertEquals('true', $modifiedRequest->getHeader('X-Added-By-Middleware'));
        $this->assertNotSame($request, $modifiedRequest);
    }

    // ==========================================
    // 11. Exception Tests
    // ==========================================

    private function createHeaderAddingMiddleware() : ClientMiddlewareInterface
    {
        return new class implements ClientMiddlewareInterface {
            public function handle(OutboundRequest $request, Closure $handler) : ClientResponse
            {
                $modified = $request->withHeaders(['X-Added-By-Middleware' => 'true']);

                return $handler($modified);
            }
        };
    }

    #[Test]
    public function middleware_pipeline_middleware_can_modify_response() : void
    {
        $addHeaderMiddleware = $this->createResponseHeaderMiddleware();

        $pipeline = new ClientMiddlewarePipeline([$addHeaderMiddleware]);
        $opts = new RequestOptions;
        $request = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);

        $response = $pipeline->execute($request, static fn (OutboundRequest $req) : ClientResponse => new ClientResponse(statusCode: 200, body: 'original'));

        $this->assertEquals(['added'], $response->getHeader('X-Response-Header'));
        $this->assertEquals('original', $response->body);
    }

    private function createResponseHeaderMiddleware() : ClientMiddlewareInterface
    {
        return new class implements ClientMiddlewareInterface {
            public function handle(OutboundRequest $request, Closure $handler) : ClientResponse
            {
                $response = $handler($request);

                return new ClientResponse(
                    statusCode: $response->statusCode,
                    headers   : array_merge($response->headers, ['X-Response-Header' => ['added']]),
                    body      : $response->body,
                );
            }
        };
    }

    #[Test]
    public function middleware_pipeline_middleware_can_short_circuit() : void
    {
        $authMiddleware = $this->createShortCircuitMiddleware();

        $pipeline = new ClientMiddlewarePipeline([$authMiddleware]);
        $opts = new RequestOptions;
        $request = new OutboundRequest(method: 'GET', url: 'https://example.com', options: $opts);

        $handlerCalled = false;
        $response = $pipeline->execute($request, static function (OutboundRequest $req) use (&$handlerCalled) : ClientResponse {
            $handlerCalled = true;

            return new ClientResponse(statusCode: 200);
        });

        $this->assertFalse($handlerCalled);
        $this->assertEquals(401, $response->statusCode);
        $this->assertEquals('Unauthorized', $response->body);
    }

    private function createShortCircuitMiddleware() : ClientMiddlewareInterface
    {
        return new class implements ClientMiddlewareInterface {
            public function handle(OutboundRequest $request, Closure $handler) : ClientResponse
            {
                return new ClientResponse(statusCode: 401, body: 'Unauthorized');
            }
        };
    }

    #[Test]
    public function http_request_failed_exception_defaults() : void
    {
        $exception = new HttpRequestFailed;
        $this->assertEquals('HTTP request failed', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->url);
        $this->assertNull($exception->method);
        $this->assertNull($exception->reason);
    }

    #[Test]
    public function http_request_failed_exception_with_context() : void
    {
        $exception = new HttpRequestFailed(
            message: 'Connection refused',
            url    : 'https://example.com/api',
            method : 'POST',
            reason : 'DNS resolution failed',
        );
        $this->assertEquals('Connection refused', $exception->getMessage());
        $this->assertEquals('https://example.com/api', $exception->url);
        $this->assertEquals('POST', $exception->method);
        $this->assertEquals('DNS resolution failed', $exception->reason);
    }

    #[Test]
    public function http_request_failed_exception_with_code() : void
    {
        $exception = new HttpRequestFailed(message: 'Error', code: 7);
        $this->assertEquals(7, $exception->getCode());
    }

    #[Test]
    public function http_request_failed_exception_chaining() : void
    {
        $previous = new RuntimeException('Original error');
        $exception = new HttpRequestFailed(message: 'Wrapped error', previous: $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    public function http_request_failed_is_runtime_exception() : void
    {
        $exception = new HttpRequestFailed;
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function http_timeout_exception_defaults() : void
    {
        $exception = new HttpTimeout;
        $this->assertEquals('HTTP request timed out', $exception->getMessage());
        $this->assertEquals(0.0, $exception->timeoutMs);
        $this->assertEquals('timeout', $exception->reason);
    }

    #[Test]
    public function http_timeout_exception_with_values() : void
    {
        $exception = new HttpTimeout(
            message  : 'Request exceeded 30s timeout',
            timeoutMs: 30000.0,
            url      : 'https://example.com/slow',
            method   : 'GET',
        );
        $this->assertEquals('Request exceeded 30s timeout', $exception->getMessage());
        $this->assertEquals(30000.0, $exception->timeoutMs);
        $this->assertEquals('https://example.com/slow', $exception->url);
        $this->assertEquals('GET', $exception->method);
        $this->assertEquals('timeout', $exception->reason);
    }

    #[Test]
    public function http_timeout_extends_http_request_failed() : void
    {
        $exception = new HttpTimeout;
        $this->assertInstanceOf(HttpRequestFailed::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function http_timeout_exception_chaining() : void
    {
        $previous = new RuntimeException('cURL timeout');
        $exception = new HttpTimeout(message: 'Request timed out', timeoutMs: 5000.0, previous: $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    #[Test]
    public function invalid_http_response_exception_defaults() : void
    {
        $exception = new InvalidHttpResponse;
        $this->assertEquals('Invalid HTTP response', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->statusCode);
        $this->assertNull($exception->url);
        $this->assertNull($exception->body);
    }

    #[Test]
    public function invalid_http_response_exception_with_context() : void
    {
        $exception = new InvalidHttpResponse(
            message   : 'Unexpected content type',
            statusCode: 200,
            url       : 'https://example.com/api',
            body      : 'Not JSON',
        );
        $this->assertEquals('Unexpected content type', $exception->getMessage());
        $this->assertEquals(200, $exception->statusCode);
        $this->assertEquals('https://example.com/api', $exception->url);
        $this->assertEquals('Not JSON', $exception->body);
    }

    #[Test]
    public function invalid_http_response_exception_with_code() : void
    {
        $exception = new InvalidHttpResponse(message: 'Error', code: 42);
        $this->assertEquals(42, $exception->getCode());
    }

    #[Test]
    public function invalid_http_response_exception_chaining() : void
    {
        $previous = new JsonException('Invalid JSON');
        $exception = new InvalidHttpResponse(message: 'Failed to decode response', statusCode: 200, previous: $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    public function invalid_http_response_is_runtime_exception() : void
    {
        $exception = new InvalidHttpResponse;
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }
}

/**
 * Helper class for tracking middleware execution order.
 */
final class TrackingMiddleware implements ClientMiddlewareInterface
{
    private string $name;

    private array $order;

    public function __construct(string $name, array &$order)
    {
        $this->name = $name;
        $this->order = &$order;
    }

    public function handle(OutboundRequest $request, Closure $handler) : ClientResponse
    {
        $this->order[] = $this->name . '-request';
        $response      = $handler($request);
        $this->order[] = $this->name . '-response';

        return $response;
    }
}
