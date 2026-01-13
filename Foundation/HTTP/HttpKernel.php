<?php

declare(strict_types=1);

namespace Avax\HTTP;

use Avax\HTTP\Dispatcher\ControllerDispatcher;
use Avax\HTTP\Middleware\MiddlewareInterface;
use Avax\HTTP\Middleware\Psr15MiddlewarePipeline;
use Avax\HTTP\Middleware\RequestHandlerInterface;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\ResponseFactory;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Routing\Exceptions\MethodNotAllowedException;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use Avax\HTTP\Router\Validation\Exceptions\ConstraintValidationException;
use Psr\Http\Message\RequestInterface;
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
     * @param RouterInterface       $router           The router for route resolution
     * @param ControllerDispatcher  $dispatcher       The controller dispatcher
     * @param MiddlewareInterface[] $globalMiddleware Always-executed middleware
     * @param ResponseFactory       $responseFactory  For error responses
     */
    public function __construct(
        private RouterInterface      $router,
        private ControllerDispatcher $dispatcher,
        private array                $globalMiddleware,
        private ResponseFactory      $responseFactory
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
            // Create the final handler (controller execution)
            $controllerHandler = new ControllerRequestHandler(router: $this->router, dispatcher: $this->dispatcher);

            // Build middleware pipeline: global → route → controller
            $pipeline = Psr15MiddlewarePipeline::create(finalHandler: $controllerHandler);

            // Add global middleware (always executed)
            foreach ($this->globalMiddleware as $middleware) {
                $pipeline = $pipeline->withMiddleware(middleware: $middleware);
            }

            // Add route-aware middleware wrapper
            $pipeline = $pipeline->withMiddleware(
                middleware: new RouteMiddlewareHandler(router: $this->router)
            );

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
            $exception instanceof RouteNotFoundException        => 404,
            $exception instanceof MethodNotAllowedException     => 405,
            $exception instanceof ConstraintValidationException => 400,
            default                                             => 500
        };

        return $this->responseFactory->createErrorResponse($statusCode, $exception->getMessage());
    }
}

/**
 * Handles controller execution after route resolution.
 *
 * @internal
 */
final readonly class ControllerRequestHandler implements RequestHandlerInterface
{
    public function __construct(
        private RouterInterface      $router,
        private ControllerDispatcher $dispatcher
    ) {}

    public function handle(RequestInterface $request) : ResponseInterface
    {
        // Router should have already resolved and set route parameters
        // We assume the request has been processed by RouteMiddlewareHandler

        // For now, return a simple response
        // In full implementation, this would dispatch to the actual controller
        return new Response(
            status : 200,
            headers: ['Content-Type' => 'application/json'],
            body   : json_encode(['message' => 'Controller executed'])
        );
    }
}

/**
 * Middleware that resolves routes and applies route-specific middleware.
 *
 * @internal
 */
final readonly class RouteMiddlewareHandler implements MiddlewareInterface
{
    public function __construct(private RouterInterface $router) {}

    public function process(
        RequestInterface        $request,
        RequestHandlerInterface $handler
    ) : ResponseInterface
    {
        // Resolve the route (this will set parameters as request attributes)
        // In a full implementation, this would call Router::resolve()

        // For now, just pass through
        return $handler->handle(request: $request);
    }
}
