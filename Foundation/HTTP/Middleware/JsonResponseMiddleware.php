<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Closure;
use Avax\HTTP\Response\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware to enforce JSON response format for API requests.
 */
readonly class JsonResponseMiddleware
{
    /**
     * Constructor method for initializing the ResponseFactory dependency.
     *
     * @param ResponseFactory $responseFactory The factory instance used to create responses.
     *
     * @return void
     */
    public function __construct(private ResponseFactory $responseFactory) {}

    /**
     * Process the request and ensure JSON response formatting.
     *
     * @param ServerRequestInterface $serverRequest The incoming request.
     * @param Closure                $next          The next middleware or handler.
     *
     * @return ResponseInterface The JSON-formatted response.
     */
    public function handle(ServerRequestInterface $serverRequest, Closure $next) : ResponseInterface
    {
        $response = $next($serverRequest);

        // Enforce JSON response if necessary
        return $this->responseFactory->response(
            data  : $response->getBody()->getContents(),
            status: $response->getStatusCode()
        );
    }
}