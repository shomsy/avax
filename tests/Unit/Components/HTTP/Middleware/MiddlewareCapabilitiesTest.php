<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Middleware;

use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\Middleware\System\PublicSurface\MiddlewareInterface;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use PHPUnit\Framework\TestCase;

final class MiddlewareCapabilitiesTest extends TestCase
{
    public function test_middleware_pipeline_executes_middleware() : void
    {
        $request  = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $middleware = new class($response) implements MiddlewareInterface {
            public function __construct(private ResponseInterface $response) {}

            public function handle(RequestInterface $request, callable $next) : ResponseInterface
            {
                return $this->response;
            }
        };

        $pipeline = new MiddlewarePipeline([$middleware]);
        $core     = function (RequestInterface $request) use ($response) : ResponseInterface {
            return $response;
        };

        $actual = $pipeline->run($request, $core);

        $this->assertSame($response, $actual);
    }
}
