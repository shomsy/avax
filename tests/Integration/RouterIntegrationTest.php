<?php

declare(strict_types=1);

namespace Avax\Tests\Integration;

use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Avax\Components\HTTP\Request\System\Configuration\RequestBuilder;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

final class RouterIntegrationTest extends TestCase
{
    private RouterRuntimeInterface $router;

    private ResponseFactory $responses;

    public function test_get_root_route_returns_200_with_correct_body_and_headers(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/'));

        self::assertSame(expected: 200, actual: $response->getStatusCode());
        self::assertStringContainsString(needle: 'Router is Working!', haystack: (string) $response->getBody());
        self::assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        self::assertStringContainsString(needle: 'text/plain', haystack: $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_get_health_route_returns_ok_body(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/health'));

        self::assertSame(expected: 'ok', actual: (string) $response->getBody());
        self::assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        self::assertStringContainsString(needle: 'text/plain', haystack: $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_get_test_route_returns_enterprise_router_message(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/test'));

        self::assertStringContainsString(
            needle: 'Enterprise Router Active!',
            haystack: (string) $response->getBody(),
        );
        self::assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        self::assertStringContainsString(needle: 'text/plain', haystack: $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_get_nonexistent_route_throws_router_failure(): void
    {
        $this->expectException(exception: RouterFailure::class);
        $this->expectExceptionMessage(message: 'Route not found');

        $this->router->resolve(request: $this->createRequest(path: '/missing'));
    }

    public function test_post_to_get_only_route_throws_router_failure(): void
    {
        $this->expectException(exception: RouterFailure::class);
        $this->expectExceptionMessage(message: 'Route not found');

        $this->router->resolve(request: $this->createRequest(method: 'POST', path: '/health'));
    }

    public function test_favicon_route_returns_204_no_content(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/favicon.ico'));

        self::assertSame(expected: 204, actual: $response->getStatusCode());
        self::assertStringContainsString(needle: 'image/x-icon', haystack: $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_all_responses_implement_response_interface(): void
    {
        foreach (['/', '/health', '/test', '/favicon.ico'] as $route) {
            $response = $this->router->resolve(request: $this->createRequest(path: $route));

            self::assertInstanceOf(
                expected: ResponseInterface::class,
                actual: $response,
                message: "Route {$route} did not return a ResponseInterface",
            );
        }
    }

    public function test_all_responses_have_content_type_header(): void
    {
        foreach (['/', '/health', '/test'] as $route) {
            $response = $this->router->resolve(request: $this->createRequest(path: $route));

            self::assertNotSame(
                expected: '',
                actual: $response->getHeaderLine(name: 'Content-Type'),
                message: "Route {$route} missing Content-Type header",
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->responses = new ResponseFactory();

        $router = new Router();
        $router->get(path: '/', action: fn () => $this->textResponse(body: 'Router is Working!'));
        $router->get(path: '/health', action: fn () => $this->textResponse(body: 'ok'));
        $router->get(path: '/test', action: fn () => $this->textResponse(body: 'Enterprise Router Active!'));
        $router->get(
            path: '/favicon.ico',
            action: fn () => $this->responses->create(
                statusCode: 204,
                headers: ['content-type' => ['image/x-icon']],
            ),
        );

        $this->router = $router;
    }

    private function createRequest(string $method = 'GET', string $path = '/'): RequestInterface
    {
        return (new RequestBuilder())
            ->withMethod(method: $method)
            ->withUri(requestUri: new RequestUri(scheme: 'http', host: 'localhost', path: $path))
            ->build();
    }

    private function textResponse(string $body, int $statusCode = 200): ResponseInterface
    {
        return $this->responses->create(
            statusCode: $statusCode,
            headers: ['content-type' => ['text/plain; charset=utf-8']],
            body: $body,
        );
    }
}
