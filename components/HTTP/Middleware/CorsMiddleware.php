<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Response\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware to add CORS headers for cross-origin requests.
 */
final readonly class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ResponseFactory|null $responseFactory = null,
    ) {}

    /**
     * Process the request and add CORS headers to the response.
     *
     * @param RequestInterface        $request The incoming request.
     * @param RequestHandlerInterface $handler The next middleware or handler.
     *
     * @return ResponseInterface The response with CORS headers.
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = strtoupper(string: $request->getMethod()) === 'OPTIONS' && $this->responseFactory !== null
            ? $this->responseFactory->createResponse(code: 204)
            : $handler->handle(request: $request);

        return $response
            ->withHeader(name: 'Access-Control-Allow-Origin', value: '*')
            ->withHeader(name: 'Access-Control-Allow-Methods', value: 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader(name: 'Access-Control-Allow-Headers', value: 'Content-Type, Authorization, X-CSRF-Token')
            ->withHeader(name: 'Access-Control-Allow-Credentials', value: 'true');
    }
}
