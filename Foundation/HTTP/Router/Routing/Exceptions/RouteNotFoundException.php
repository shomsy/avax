<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Exceptions;

/**
 * Thrown when no route matches the incoming request.
 *
 * Represents HTTP 404 Not Found errors in the routing layer.
 * Provides structured context for debugging and monitoring.
 */
final class RouteNotFoundException extends RouterException
{
    /**
     * Factory for standard 404 message.
     *
     * @param string $method HTTP method used.
     * @param string $path   URI path attempted.
     *
     * @return static
     */
    public static function for(string $method, string $path) : self
    {
        return new self(
            message: sprintf('No route found for [%s] %s', strtoupper($method), $path),
            httpStatusCode: 404,
            context: [
                'method' => $method,
                'path' => $path,
                'available_methods' => [], // Can be populated by caller
            ],
            isRetryable: false
        );
    }

    /**
     * Get the HTTP status code (404).
     */
    public function getHttpStatusCode() : int
    {
        return 404;
    }
}