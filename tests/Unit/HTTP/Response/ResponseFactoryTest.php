<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\HTTP\Response;

use Avax\Components\HTTP\Response\Responses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Responses.
 */
final class ResponsesTest extends TestCase
{
    private Responses $factory;

    #[Test]
    public function create_makes_basic_response() : void
    {
        $response = $this->factory->create(statusCode: 200, headers: [], body: 'Hello');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello', (string) $response->getBody());
    }

    #[Test]
    public function create_with_custom_status_code() : void
    {
        $response = $this->factory->create(statusCode: 404);

        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function create_with_headers() : void
    {
        $response = $this->factory->create(
            statusCode: 200,
            headers   : ['X-Custom' => ['test-value']],
        );

        $this->assertArrayHasKey('x-custom', array_change_key_case($response->getHeaders()));
    }

    #[Test]
    public function json_creates_json_response() : void
    {
        $response = $this->factory->json(data: ['message' => 'success']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string) $response->getBody(), associative: true);
        $this->assertEquals('success', $body['message']);
    }

    #[Test]
    public function json_with_custom_status() : void
    {
        $response = $this->factory->json(data: ['error' => 'Not Found'], statusCode: 404);

        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function json_with_additional_headers() : void
    {
        $response = $this->factory->json(
            data      : ['data' => 'test'],
            statusCode: 200,
            headers   : ['X-Custom' => ['header-value']],
        );

        $body = json_decode((string) $response->getBody(), associative: true);
        $this->assertEquals('test', $body['data']);
    }

    #[Test]
    public function html_creates_html_response() : void
    {
        $response = $this->factory->html(html: '<h1>Hello</h1>');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        $this->assertEquals('<h1>Hello</h1>', (string) $response->getBody());
    }

    #[Test]
    public function html_with_custom_status() : void
    {
        $response = $this->factory->html(html: '<h1>Error</h1>', statusCode: 500);

        $this->assertEquals(500, $response->getStatusCode());
    }

    #[Test]
    public function redirect_creates_redirect_response() : void
    {
        $response = $this->factory->redirect(url: '/dashboard');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/dashboard', $response->getHeaderLine('Location'));
    }

    #[Test]
    public function redirect_with_custom_status() : void
    {
        $response = $this->factory->redirect(url: '/new-url', statusCode: 301);

        $this->assertEquals(301, $response->getStatusCode());
    }

    #[Test]
    public function not_found_creates_404_json_response() : void
    {
        $response = $this->factory->notFound(message: 'Resource not found');

        $this->assertEquals(404, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), associative: true);
        $this->assertEquals('Resource not found', $body['message']);
    }

    #[Test]
    public function error_creates_error_json_response() : void
    {
        $response = $this->factory->error(message: 'Internal Server Error', statusCode: 500);

        $this->assertEquals(500, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), associative: true);
        $this->assertEquals('Internal Server Error', $body['message']);
        $this->assertTrue($body['error']);
    }

    #[Test]
    public function error_defaults_to_500() : void
    {
        $response = $this->factory->error(message: 'Error');

        $this->assertEquals(500, $response->getStatusCode());
    }

    #[Test]
    public function rate_limited_creates_429_response() : void
    {
        $response = $this->factory->rateLimited(retryAfter: 120);

        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals('120', $response->getHeaderLine('Retry-After'));

        $body = json_decode((string) $response->getBody(), associative: true);
        $this->assertEquals(120, $body['retry_after']);
    }

    #[Test]
    public function no_content_creates_204_response() : void
    {
        $response = $this->factory->noContent();

        $this->assertEquals(204, $response->getStatusCode());
    }

    #[Test]
    public function empty_creates_empty_response() : void
    {
        $response = $this->factory->empty(statusCode: 200);

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function empty_with_custom_status() : void
    {
        $response = $this->factory->empty(statusCode: 202);

        $this->assertEquals(202, $response->getStatusCode());
    }

    #[Test]
    public function json_encodes_complex_data() : void
    {
        $data = [
            'user' => [
                'name' => 'John',
                'roles' => ['admin', 'editor'],
            ],
            'meta' => [
                'page' => 1,
                'total' => 100,
            ],
        ];

        $response = $this->factory->json(data: $data);
        $body = json_decode((string) $response->getBody(), associative: true);

        $this->assertEquals('John', $body['user']['name']);
        $this->assertContains('admin', $body['user']['roles']);
        $this->assertEquals(100, $body['meta']['total']);
    }

    protected function setUp() : void
    {
        $this->factory = new Responses();
    }
}
