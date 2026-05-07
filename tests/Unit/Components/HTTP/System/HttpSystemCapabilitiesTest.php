<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\System;

use Avax\Components\HTTP\Middleware\System\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\System\PublicSurface\RouterInterface;
use Avax\Components\HTTP\System\System\PublicSurface\Http;
use PHPUnit\Framework\TestCase;

final class HttpSystemCapabilitiesTest extends TestCase
{
    public function test_http_kernel_handles_request_through_pipeline() : void
    {
        $router   = $this->createMock(RouterInterface::class);
        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $router->method('dispatch')->willReturn($response);

        $pipeline = new MiddlewarePipeline([]);

        $http   = new Http($router, $pipeline);
        $actual = $http->handle($request);

        $this->assertSame($response, $actual);
    }
}
