<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware to add CORS headers for cross-origin requests.
 */
class CorsMiddleware
{
    /**
     * Process the request and add CORS headers to the response.
     *
     * @param ServerRequestInterface $serverRequest The incoming request.
     * @param Closure $next The next middleware or handler.
     *
     * @return ResponseInterface The response with CORS headers.
     */
    public function handle(ServerRequestInterface $serverRequest, Closure $next): ResponseInterface
    {
        $response = $next($serverRequest);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    }
}