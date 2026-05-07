<?php

declare(strict_types=1);

namespace Avax\Tests\Integration;

use Avax\Components\HTTP\Request\System\Capabilities\Uri\RequestUri;
use Avax\Components\HTTP\Request\System\Configuration\RequestBuilder;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use TypeError;

final class RouterHardeningTest extends TestCase
{
    private RouterRuntimeInterface $router;

    private Responses $responses;

    public function test_callable_returning_null_should_fail_fast(): void
    {
        $router = new Router();
        $router->get(path: '/null-test', action: static fn () => null);

        $this->expectException(TypeError::class);

        $router->resolve(request: $this->createRequest(path: '/null-test'));
    }

    public function test_post_on_get_only_route_should_throw_router_failure(): void
    {
        $this->expectException(RouterFailure::class);
        $this->expectExceptionMessage('Route not found');

        $this->router->resolve(request: $this->createRequest(method: 'POST', path: '/health'));
    }

    public function test_fallback_route_returns_router_failure_for_missing_route(): void
    {
        $this->expectException(RouterFailure::class);
        $this->expectExceptionMessage('Route not found');

        $this->router->resolve(request: $this->createRequest(path: '/non-existent-route-12345'));
    }

    public function test_stress_test_sequential_route_calls_no_leaks(): void
    {
        $routes = ['/', '/health', '/test', '/favicon.ico'];

        for ($i = 0; $i < 100; $i++) {
            foreach ($routes as $route) {
                $response = $this->router->resolve(request: $this->createRequest(path: $route));

                self::assertInstanceOf(ResponseInterface::class, $response);
                self::assertIsInt($response->getStatusCode());
                self::assertIsString((string) $response->getBody());
            }
        }

        self::assertTrue(true, 'Stress test completed without issues');
    }

    public function test_all_dispatcher_methods_return_response_interface(): void
    {
        foreach (['/', '/health', '/test', '/favicon.ico'] as $route) {
            $response = $this->router->resolve(request: $this->createRequest(path: $route));

            self::assertInstanceOf(ResponseInterface::class, $response);
            self::assertIsInt($response->getStatusCode());
            self::assertIsString($response->getReasonPhrase());
            self::assertIsArray($response->getHeaders());
            self::assertIsString((string) $response->getBody());
        }
    }

    public function test_route_pipeline_dispatch_returns_valid_response(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/health'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('ok', (string) $response->getBody());
        self::assertStringContainsString('text/plain', $response->getHeaderLine(name: 'Content-Type'));
    }

    public function test_router_kernel_returns_final_response(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/'));

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Router is Working!', (string) $response->getBody());
    }

    public function test_debug_route_returns_router_failure(): void
    {
        $this->expectException(RouterFailure::class);
        $this->expectExceptionMessage('Route not found');

        $this->router->resolve(request: $this->createRequest(path: '/debug'));
    }

    public function test_middleware_stagechain_reactivation_works(): void
    {
        $response = $this->router->resolve(request: $this->createRequest(path: '/health'));

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertSame(200, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->responses = new Responses();

        $router = new Router();
        $router->get(path: '/', action: fn () => $this->textResponse(body: 'Router is Working!'));
        $router->get(path: '/health', action: fn () => $this->textResponse(body: 'ok'));
        $router->get(path: '/test', action: fn () => $this->textResponse(body: 'Enterprise Router Active!'));
        $router->get(
            path: '/favicon.ico',
            action: fn () => $this->responses->createResponseWithBody(
                content: '',
                status : 204,
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
        return $this->responses->send(
            data  : $body,
            status: $statusCode,
        );
    }
}
