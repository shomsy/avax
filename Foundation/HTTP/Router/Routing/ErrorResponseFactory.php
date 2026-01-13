<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use Psr\Http\Message\ResponseInterface;

/**
 * Central factory for creating error responses (404, 405, etc.).
 *
 * Ensures consistent error handling and formatting across the router.
 */
final readonly class ErrorResponseFactory
{
    public function createNotFoundResponse(string $method, string $path) : ResponseInterface
    {
        $body = sprintf('Route not found for [%s] %s', $method, $path);

        return new Response(
            stream    : Stream::fromString(content: $body),
            statusCode: 404,
            headers   : ['Content-Type' => 'text/plain'],
        );
    }

    public function createMethodNotAllowedResponse(string $method, string $path, array $allowedMethods) : ResponseInterface
    {
        $body = sprintf('Method %s not allowed for %s. Allowed: %s', $method, $path, implode(', ', $allowedMethods));

        return new Response(
            stream    : Stream::fromString(content: $body),
            statusCode: 405,
            headers   : [
                'Content-Type' => 'text/plain',
                'Allow'        => implode(', ', $allowedMethods),
            ],
        );
    }
}
