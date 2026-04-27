<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Kernel;

use Avax\Components\HTTP\System\Flows\Routing\ResolveRouteFromHttpRequest;
use Avax\HTTP\Middleware\Psr15MiddlewarePipeline;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\System\Foundation\Exceptions\InvalidConstraintException;
use Avax\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * HTTP Kernel Implementation
 *
 * Orchestrates HTTP request processing through:
 * 1. Global middleware pipeline
 * 2. Router resolution
 * 3. Route-specific middleware
 * 4. Controller execution
 *
 * @internal
 */
final readonly class HttpKernel implements Kernel
{
    /**
     * @param array           $globalMiddleware Always-executed middleware
     * @param ResponseFactory $responseFactory  For error responses
     */
    public function __construct(
        private RouterRuntimeInterface $router,
        private array                  $globalMiddleware,
        private ResponseFactory        $responseFactory
    ) {}

    /**
     * Process HTTP request through the complete pipeline.
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            $pipeline = Psr15MiddlewarePipeline::create(
                finalHandler: new ResolveRouteFromHttpRequest(router: $this->router)
            );

            foreach ($this->globalMiddleware as $middleware) {
                $pipeline = $pipeline->withMiddleware(middleware: $middleware);
            }

            return $pipeline->handle(request: $request);

        } catch (Throwable $exception) {
            return $this->handleException(exception: $exception);
        }
    }

    /**
     * Convert exceptions to HTTP error responses.
     */
    private function handleException(Throwable $exception) : ResponseInterface
    {
        $statusCode = match (true) {
            $exception instanceof RouteNotFoundException     => 404,
            $exception instanceof MethodNotAllowedException  => 405,
            $exception instanceof InvalidConstraintException => 400,
            default                                          => 500
        };

        return $this->responseFactory->createErrorResponse(statusCode: $statusCode, message: $exception->getMessage());
    }
}
