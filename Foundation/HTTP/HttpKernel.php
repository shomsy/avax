<?php

declare(strict_types=1);

namespace Avax\HTTP;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\MiddlewareInterface;
use Avax\HTTP\Middleware\Psr15MiddlewarePipeline;
use Avax\HTTP\Middleware\RequestHandlerInterface;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\RouterRuntimeInterface;
use Avax\HTTP\Router\System\Foundation\Exceptions\InvalidConstraintException;
use Avax\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
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
    private ResponseFactory      $responseFactory;
    private array                $globalMiddleware;
    private ControllerDispatcher $dispatcher;
    private RouterRuntimeInterface $router;

    /**
     * @param RouterInterface       $router           The router for route resolution
     * @param ControllerDispatcher  $dispatcher       The controller dispatcher
     * @param MiddlewareInterface[] $globalMiddleware Always-executed middleware
     * @param ResponseFactory       $responseFactory  For error responses
     */
    public function __construct(
        RouterRuntimeInterface $router,
        ControllerDispatcher $dispatcher,
        array                $globalMiddleware,
        ResponseFactory      $responseFactory
    )
    {
        $this->router           = $router;
        $this->dispatcher       = $dispatcher;
        $this->globalMiddleware = $globalMiddleware;
        $this->responseFactory  = $responseFactory;
    }

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

/**
 * Handles controller execution after route resolution.
 *
 * @internal
 */
final readonly class RouterRequestHandler implements RequestHandlerInterface
{
    public function __construct(private RouterRuntimeInterface $router) {}

    public function handle(RequestInterface $request) : ResponseInterface
    {
        if (! $request instanceof ServerRequest) {
            throw new RuntimeException(
                message: 'HttpKernel requires an internal Avax HTTP ServerRequest instance for router execution.'
            );
        }

        return $this->router->resolve(request: $request);
    }
}
