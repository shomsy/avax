<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\HTTP\Response\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-15 Middleware to enforce JSON response format for API requests.
 */
readonly class JsonResponseMiddleware implements MiddlewareInterface
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
     * PSR-15 process method: intercept and modify responses to ensure JSON format.
     *
     * @param RequestInterface        $request The incoming request.
     * @param RequestHandlerInterface $handler The next handler in the chain.
     *
     * @return ResponseInterface The JSON-formatted response.
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle(request: $request);

        // Enforce JSON response if necessary
        return $this->responseFactory->response(
            data  : $response->getBody()->getContents(),
            status: $response->getStatusCode()
        );
    }
}
