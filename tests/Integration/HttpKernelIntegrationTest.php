<?php

declare(strict_types=1);

namespace Avax\Tests\Integration;

use Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\System\Capabilities\Kernel\HttpKernel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the HTTP Kernel request lifecycle.
 */
final class HttpKernelIntegrationTest extends TestCase
{
    private RouterInterface $router;

    private HttpKernel $kernel;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->kernel = new HttpKernel(router: $this->router);
    }

    #[Test]
    public function kernel_dispatches_request_to_router(): void
    {
        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->router->expects($this->once())
            ->method('dispatch')
            ->with($request)
            ->willReturn($response);

        $result = $this->kernel->handle(request: $request);

        $this->assertSame($response, $result);
    }

    #[Test]
    public function kernel_executes_middleware_in_order(): void
    {
        $executionOrder = [];

        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware1->method('handle')
            ->willReturnCallback(static function ($request, $next) use (&$executionOrder) {
                $executionOrder[] = 'before-1';
                $response         = $next($request);
                $executionOrder[] = 'after-1';

                return $response;
            });

        $middleware2 = $this->createMock(MiddlewareInterface::class);
        $middleware2->method('handle')
            ->willReturnCallback(static function ($request, $next) use (&$executionOrder) {
                $executionOrder[] = 'before-2';
                $response         = $next($request);
                $executionOrder[] = 'after-2';

                return $response;
            });

        $this->kernel->use($middleware1);
        $this->kernel->use($middleware2);

        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->router->method('dispatch')->willReturn($response);

        $this->kernel->handle(request: $request);

        $this->assertEquals(['before-1', 'before-2', 'after-2', 'after-1'], $executionOrder);
    }

    #[Test]
    public function middleware_can_short_circuit_request(): void
    {
        $blockedResponse = $this->createMock(ResponseInterface::class);

        $blockingMiddleware = $this->createMock(MiddlewareInterface::class);
        $blockingMiddleware->method('handle')
            ->willReturnCallback(static fn ($request, $next) => $blockedResponse);

        $this->kernel->use($blockingMiddleware);

        $request = $this->createMock(RequestInterface::class);

        // Router should not be called
        $this->router->expects($this->never())
            ->method('dispatch');

        $result = $this->kernel->handle(request: $request);

        $this->assertSame($blockedResponse, $result);
    }

    #[Test]
    public function kernel_tracks_registered_middleware(): void
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);

        $this->kernel->use($middleware1);
        $this->kernel->use($middleware2);

        $registered = $this->kernel->middleware();

        $this->assertCount(2, $registered);
        $this->assertContains($middleware1, $registered);
        $this->assertContains($middleware2, $registered);
    }

    #[Test]
    public function kernel_exposes_router(): void
    {
        $this->assertSame($this->router, $this->kernel->router());
    }

    #[Test]
    public function kernel_tracks_boot_state(): void
    {
        $this->assertFalse($this->kernel->isBooted());

        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $this->router->method('dispatch')->willReturn($response);

        $this->kernel->handle(request: $request);

        $this->assertTrue($this->kernel->isBooted());
    }
}
