<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Kernel;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\MiddlewareInterface;
use Avax\Components\HTTP\System\PublicSurface\HttpInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application Kernel - Complete HTTP Runtime
 *
 * Combines Router, Kernel, and PSR-15 middleware pipeline into
 * a production-ready HTTP application runtime.
 *
 * Middleware stack is assembled in Configuration and injected.
 * Runtime execution only runs the pipeline — no composition.
 */
final readonly class AppKernel implements HttpInterface, Kernel
{
    public function __construct(
        private RouterRuntimeInterface $routerRuntime,
        private CreateHttpResponse        $createHttpResponse,
        /** @var list<MiddlewareInterface> */
        private array $middlewareStack = [],
    ) {
    }

    /**
     * @return array<string, int>
     */
    public static function getMiddlewarePriorityHints() : array
    {
        return [];
    }

    public function handleRequest(RequestInterface $request) : ResponseInterface
    {
        return $this->handle($request);
    }

    public function handle(ServerRequestInterface $serverRequest) : ResponseInterface
    {
        if (! $serverRequest instanceof RequestInterface) {
            return $this->createHttpResponse->error(message: 'Invalid request type', status: 400);
        }

        return $this->runPipeline($serverRequest);
    }

    private function runPipeline(RequestInterface $request) : ResponseInterface
    {
        $core = fn (RequestInterface $request) : ResponseInterface => $this->routerRuntime->resolve($request);

        $pipeline = $this->middlewareStack;

        while ( $middleware = array_pop($pipeline) ) {
            $core = static fn (RequestInterface $request) => $middleware->handle($request, $core);
        }

        return $core($request);
    }

    public function terminate(RequestInterface $request, ResponseInterface $response) : void {}

    public function getRouter() : RouterRuntimeInterface
    {
        return $this->routerRuntime;
    }

    public function withMiddleware(MiddlewareInterface $middleware) : self
    {
        return new self(
            $this->routerRuntime,
            $this->createHttpResponse,
            [...$this->middlewareStack, $middleware],
        );
    }
}
