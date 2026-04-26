<?php

declare(strict_types=1);

namespace Avax\HTTP;

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
* @param array                  $globalMiddleware Always-executed middleware
* @param ResponseFactory        $responseFactory  for error responses
     */
    public function __construct(
        private RouterRuntimeInterface $router,
        private array                  $globalMiddleware,
        private ResponseFactory        $responseFactory
    ) {}

    /**
     * Process HTTP request through the complete pipeline.
     *
     * DEFINITIVE REQUEST LIFECYCLE:
     * 1. Apply global middleware
     * 2. Router resolves route + extracts parameters
     * 3. Apply route-specific middleware
     * 4. Execute controller action
     * 5. Return response
     *
     * All exceptions are caught and converted to error responses.
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            $pipeline = Psr15MiddlewarePipeline::create(
                finalHandler: new RouterRequestHandler(router: $this->router)
            );

            // Add global middleware (always executed)
            foreach ($this->globalMiddleware as $middleware) {
                $pipeline = $pipeline->withMiddleware(middleware: $middleware);
            }

            // Execute the complete pipeline
            return $pipeline->handle(request: $request);

        } catch (Throwable $exception) {
            // Centralized exception boundary
            return $this->handleException(exception: $exception);
        }
    }

    /**
     * Convert exceptions to HTTP error responses.
     */
    private function handleException(Throwable $exception) : ResponseInterface
    {
        // Map common exceptions to HTTP status codes
        $statusCode = match (true) {
            $exception instanceof RouteNotFoundException     => 404,
            $exception instanceof MethodNotAllowedException  => 405,
            $exception instanceof InvalidConstraintException => 400,
            default                                          => 500
        };

        return $this->responseFactory->createErrorResponse(statusCode: $statusCode, message: $exception->getMessage());
    }
}
