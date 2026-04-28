<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

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
    private RequestHandlerInterface $finalHandler;
    private array                   $middleware;

    /**
     * @param MiddlewareInterface[]   $middleware   Stack of middleware (immutable)
     * @param RequestHandlerInterface $finalHandler Handler called after all middleware
     */
    public function __construct(
        array                   $middleware,
        RequestHandlerInterface $finalHandler
    )
    {
        $this->middleware   = $middleware;
        $this->finalHandler = $finalHandler;
    }

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

        foreach (array_reverse(array: $this->middleware) as $middleware) {
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
    private RequestHandlerInterface $next;
    private MiddlewareInterface     $middleware;

    public function __construct(
        MiddlewareInterface     $middleware,
        RequestHandlerInterface $next
    )
    {
        $this->middleware = $middleware;
        $this->next       = $next;
    }

    public function handle(RequestInterface $request) : ResponseInterface
    {
        return $this->middleware->process(request: $request, handler: $this->next);
    }
}
