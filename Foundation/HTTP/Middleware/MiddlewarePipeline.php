<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\HTTP\Response\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * The MiddlewarePipeline class is responsible for managing and executing
 * a stack of middleware components. The middleware components are processed
 * based on their priority to generate a valid HTTP response.
 */
class MiddlewarePipeline
{
    /**
     * @var array The stack of middleware components, each with an associated priority.
     */
    private array $middlewareStack = [];

    /**
     * Adds middleware with a specified priority to the pipeline.
     *
     * The middleware stack is sorted based on priority after new middleware is added.
     *
     * @param callable $middleware The middleware to add.
     * @param int      $priority   The priority of the middleware. Lower values indicate higher priority.
     */
    public function add(callable $middleware, int $priority = 10) : void
    {
        $this->middlewareStack[] = ['middleware' => $middleware, 'priority' => $priority];
        usort($this->middlewareStack, static fn(array $a, array $b) : int => $a['priority'] <=> $b['priority']);
    }

    /**
     * Executes the middleware pipeline.
     *
     * Each middleware in the stack is executed until a valid ResponseInterface instance is produced.
     * Middleware components call the `$next` callable to proceed to the next middleware.
     *
     * @param RequestInterface $request The HTTP request to process.
     *
     * @return ResponseInterface The HTTP response produced by the middleware stack.
     *
     * @throws MiddlewareExecutionException If the pipeline does not produce a response.
     */
    public function execute(RequestInterface $request) : ResponseInterface
    {
        // Create the final handler
        $finalHandler = static fn($req) : ResponseFactory|ResponseInterface => response(
            status : 200,
            headers: ['Content-Type' => 'text/plain'],
            body   : 'Default OK'
        );


        // Build the middleware chain
        $next = $finalHandler;

        foreach (array_reverse($this->middlewareStack) as $entry) {
            $middleware = $entry['middleware'];

            $next = static fn($req) => $middleware($req, $next);
        }

        // Execute the pipeline
        try {
            $response = $next($request);

            // Ensure the response is valid
            if (! $response instanceof ResponseInterface) {
                throw new MiddlewareExecutionException(
                    message: 'Pipeline did not produce a valid ResponseInterface'
                );
            }

            return $response;
        } catch (Throwable $throwable) {
            throw new MiddlewareExecutionException(
                message : 'Middleware execution failed',
                code    : $throwable->getCode(),
                previous: $throwable
            );
        }
    }
}
