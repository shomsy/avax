<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Middleware;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Closure;

/**
 * ClientMiddlewarePipeline - Chains multiple middleware into a single handler.
 *
 * Wraps each middleware around the next, creating a pipeline where
 * requests flow through each middleware before reaching the final handler,
 * and responses flow back through each middleware in reverse order.
 *
 * Usage:
 *   $pipeline = new ClientMiddlewarePipeline([
 *       new LoggingMiddleware(),
 *       new AuthMiddleware(),
 *       new RetryMiddleware(),
 *   ]);
 *   $handler = $pipeline->resolve(fn($req) => $transport->send($req));
 *   $response = $handler($request);
 */
final readonly class ClientMiddlewarePipeline
{
    /**
     * @param list<ClientMiddlewareInterface> $middlewares Ordered list of middleware
     */
    public function __construct(
        public array $middlewares = [],
    ) {}

    /**
     * Add middleware to the pipeline.
     *
     * Returns a new pipeline instance (immutable).
     */
    public function with(ClientMiddlewareInterface $middleware) : self
    {
        return new self([...$this->middlewares, $middleware]);
    }

    /**
     * Execute the pipeline with a request and final handler.
     *
     * Convenience method that resolves and executes in one call.
     *
     * @param OutboundRequest                           $request      The outbound request
     * @param Closure(OutboundRequest) : ClientResponse $finalHandler The terminal handler
     */
    public function execute(OutboundRequest $request, Closure $finalHandler) : ClientResponse
    {
        $handler = $this->resolve($finalHandler);

        return $handler($request);
    }

    /**
     * Resolve the middleware chain into a single handler.
     *
     * Wraps each middleware around the final handler, creating
     * a composed function that processes requests through the entire chain.
     *
     * @param Closure(OutboundRequest) : ClientResponse $finalHandler The terminal handler
     *
     * @return Closure(OutboundRequest) : ClientResponse The composed handler
     */
    public function resolve(Closure $finalHandler) : Closure
    {
        // Start with the final handler
        $handler = $finalHandler;

        // Wrap each middleware around the handler in reverse order
        // (so the first middleware executes first)
        for ($i = count($this->middlewares) - 1; $i >= 0; $i--) {
            $middleware     = $this->middlewares[$i];
            $currentHandler = $handler;
            $handler        = static fn (OutboundRequest $request) => $middleware->handle($request, $currentHandler);
        }

        return $handler;
    }

    /**
     * Get the number of middleware in the pipeline.
     */
    public function count(): int
    {
        return count($this->middlewares);
    }

    /**
     * Check if the pipeline has any middleware.
     */
    public function isEmpty(): bool
    {
        return empty($this->middlewares);
    }
}
