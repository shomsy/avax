<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-15 Compatible Middleware Pipeline
 *
 * Immutable middleware pipeline that processes requests through a chain
 * of PSR-15 middleware components.
 *
 * @internal
 */
final readonly class Psr15MiddlewarePipeline implements RequestHandlerInterface
{
    /**
     * @param MiddlewareInterface[]   $middleware   Stack of middleware (immutable)
     * @param RequestHandlerInterface $finalHandler Handler called after all middleware
     */
    public function __construct(
        private array                   $middleware,
        private RequestHandlerInterface $finalHandler
    ) {}

    /**
     * Create an empty pipeline with a final handler.
     */
    public static function create(RequestHandlerInterface $finalHandler) : self
    {
        return new self(middleware: [], finalHandler: $finalHandler);
    }

    /**
     * Add middleware to the pipeline (returns new immutable instance).
     */
    public function withMiddleware(MiddlewareInterface $middleware) : self
    {
        return new self(
            middleware  : [...$this->middleware, $middleware],
            finalHandler: $this->finalHandler
        );
    }

    /**
     * Process the request through the middleware chain.
     */
    public function handle(RequestInterface $request) : ResponseInterface
    {
        // Build the middleware chain from the inside out
        $handler = $this->finalHandler;

        foreach (array_reverse($this->middleware) as $middleware) {
            $handler = new MiddlewareHandler(middleware: $middleware, next: $handler);
        }

        return $handler->handle(request: $request);
    }
}

/**
 * Internal handler that wraps a middleware and delegates to the next handler.
 *
 * @internal
 */
final readonly class MiddlewareHandler implements RequestHandlerInterface
{
    public function __construct(
        private MiddlewareInterface     $middleware,
        private RequestHandlerInterface $next
    ) {}

    public function handle(RequestInterface $request) : ResponseInterface
    {
        return $this->middleware->process(request: $request, handler: $this->next);
    }
}
