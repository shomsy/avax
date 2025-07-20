<?php

declare(strict_types=1);

namespace Gemini\HTTP\Router\Routing\Exceptions;

use RuntimeException;

/**
 * Thrown when no route matches the incoming request.
 */
final class RouteNotFoundException extends RuntimeException
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
            code   : 404
        );
    }
}
