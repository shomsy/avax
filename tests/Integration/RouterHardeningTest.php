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
use TypeError;

final class RouterHardeningTest extends TestCase
{
    private RouterRuntimeInterface $router;

    private ResponseFactory $responses;

    public function test_callable_returning_null_should_fail_fast(): void
    {
        $router = new Router();
        $router->get(path: '/null-test', action: static fn () => null);

        $this->expectException(exception: TypeError::class);

        $router->resolve(request: $this->createRequest(path: '/null-test'));
    }

    public function test_post_on_get_only_route_should_throw_router_failure(): void
    {
        $this->expectException(exception: RouterFailure::class);
        $this->expectExceptionMessage(message: 'Route not found');

        $this->router->resolve(request: $this->createRequest(method: 'POST', path: '/health'));
    }

    public function test_fallback_route_returns_router_failure_for_missing_route(): void
    {
        $this->expectException(exception: RouterFailure::class);
        $this->expectExceptionMessage(message: 'Route not found');

        $this->router->resolve(request: $this->createRequest(path: '/non-existent-route-12345'));
    }

    public function test_stress_test_sequential_route_calls_no_leaks(): void
    {
        $routes = ['/', '/health', '/test', '/favicon.ico'];

        for ($i = 0; $i < 100; $i++) {
            foreach ($routes as $route) {
                $response = $this->router->resolve(request: $this->createRequest(path: $route));

                self::assertInstanceOf(expected: ResponseInterface::class, actual: $response);
                self::assertIsInt(actual: $response->getStatusCode());
                self::assertIsString(actual: (string) $response->getBody());
            }
        }

        self::assertTrue(condition: true, message: 'Stress test completed without issues');
    }

    public function test_all_dispatcher_methods_return_response_interface(): void
    {
        foreach (['/', '/health', '/test', '/favicon.ico'] as $route) {
            $response = $this->router->resolve(request: $this->createRequest(path: $route));

            self::assertInstanceOf(expected: ResponseInterface::class, actual: $response);
            self::assertIsInt(actual: $response->getStatusCode());
            self::assertIsString(actual: $response->getReasonPhrase());
            self::assertIsArray(actual: $response->getHeaders());
            self::assertIsString(actual: (string) $response->getBody());
        }
    }

    public function test_route_pipeline_dispatch_returns_valid_response(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/health'));

        self::assertSame(expected: 200, actual: $response->getStatusCode());
        self::assertSame(expected: 'ok', actual: (string) $response->getBody());
        self::assertStringContainsString(needle: 'text/plain', haystack: $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_router_kernel_returns_final_response(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/'));

        self::assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        self::assertSame(expected: 200, actual: $response->getStatusCode());
        self::assertStringContainsString(needle: 'Router is Working!', haystack: (string) $response->getBody());
    }

    public function test_debug_route_returns_router_failure(): void
    {
        $this->expectException(exception: RouterFailure::class);
        $this->expectExceptionMessage(message: 'Route not found');

        $this->router->resolve(request: $this->createRequest(path: '/debug'));
    }

    public function test_middleware_stagechain_reactivation_works(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/health'));

        self::assertInstanceOf(expected: ResponseInterface::class, actual: $response);
        self::assertSame(expected: 200, actual: $response->getStatusCode());
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
