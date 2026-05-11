<?php

declare(strict_types=1);

namespace Avax\Tests\E2E;

use Avax\Framework\System\PublicSurface\App;
use Avax\Framework\System\PublicSurface\Avax;
use Avax\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * E2ERuntimeTest — end-to-end proof that AvaX works as a framework.
 *
 * V5-22: These tests exercise the full framework stack through the public App API,
 * proving that routing, request handling, response creation, and error handling
 * work together as a system — not just as isolated components.
 */
final class E2ERuntimeTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $originalServer = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    private function createApp(): App
    {
        return Avax::create(environment: 'testing');
    }

    public function test_app_creates_and_resolves_hello_world(): void
    {
        $app = $this->createApp();
        $app->get('/hello', fn() => 'Hello World');

        $response = $this->simulateRequest($app, 'GET', '/hello');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Hello World', (string) $response->getBody());
    }

    public function test_app_handles_json_response(): void
    {
        $app = $this->createApp();
        $app->get('/api/user', fn() => ['name' => 'AvaX', 'version' => 5]);

        $response = $this->simulateRequest($app, 'GET', '/api/user');

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertJson($body);
        $data = json_decode($body, true);
        self::assertSame('AvaX', $data['name']);
    }

    public function test_app_returns_404_for_unknown_route(): void
    {
        $app = $this->createApp();
        $app->get('/exists', fn() => 'OK');

        $response = $this->simulateRequest($app, 'GET', '/does-not-exist');

        self::assertSame(404, $response->getStatusCode());
    }

    public function test_app_handles_different_http_methods(): void
    {
        $app = $this->createApp();
        $app->get('/resource', fn() => 'GET');
        $app->post('/resource', fn() => 'POST');
        $app->put('/resource', fn() => 'PUT');
        $app->delete('/resource', fn() => 'DELETE');

        self::assertSame('GET', (string) $this->simulateRequest($app, 'GET', '/resource')->getBody());
        self::assertSame('POST', (string) $this->simulateRequest($app, 'POST', '/resource')->getBody());
        self::assertSame('PUT', (string) $this->simulateRequest($app, 'PUT', '/resource')->getBody());
        self::assertSame('DELETE', (string) $this->simulateRequest($app, 'DELETE', '/resource')->getBody());
    }

    public function test_app_handles_multiple_different_paths(): void
    {
        $app = $this->createApp();
        $app->get('/users/42', fn() => 'User: 42');
        $app->get('/users/99', fn() => 'User: 99');

        $response = $this->simulateRequest($app, 'GET', '/users/42');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('User: 42', (string) $response->getBody());
    }

    public function test_app_handles_exception_through_error_handler(): void
    {
        $app = $this->createApp();
        $app->get('/boom', fn() => throw new \RuntimeException('Something went wrong'));

        $response = $this->simulateRequest($app, 'GET', '/boom');

        self::assertSame(500, $response->getStatusCode());
    }

    public function test_app_handles_multiple_requests_without_state_leak(): void
    {
        $app = $this->createApp();
        $app->get('/counter', fn() => 'OK');

        $response1 = $this->simulateRequest($app, 'GET', '/counter');
        $response2 = $this->simulateRequest($app, 'GET', '/counter');

        self::assertSame(200, $response1->getStatusCode());
        self::assertSame(200, $response2->getStatusCode());
    }

    public function test_app_registers_and_matches_multiple_routes(): void
    {
        $app = $this->createApp();
        $app->get('/a', fn() => 'A');
        $app->get('/b', fn() => 'B');
        $app->get('/c', fn() => 'C');
        $app->get('/a/b/c', fn() => 'ABC');

        self::assertStringContainsString('A', (string) $this->simulateRequest($app, 'GET', '/a')->getBody());
        self::assertStringContainsString('B', (string) $this->simulateRequest($app, 'GET', '/b')->getBody());
        self::assertStringContainsString('C', (string) $this->simulateRequest($app, 'GET', '/c')->getBody());
        self::assertStringContainsString('ABC', (string) $this->simulateRequest($app, 'GET', '/a/b/c')->getBody());
    }

    /**
     * Simulates an HTTP request through the App without running a real server.
     * This is the E2E proof that the framework stack works together.
     */
    private function simulateRequest(App $app, string $method, string $uri): ResponseInterface
    {
        // V5-22: Clean $_SERVER to avoid contamination from other tests.
        $_SERVER = [
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $uri,
            'HTTP_HOST' => 'localhost',
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
        ];

        return $app->run();
    }
}
