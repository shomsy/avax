<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\RunRoute\Responses;

use Avax\HTTP\Response\Response;
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

        return Response::text(content: $body, status: 404);
    }

    public function createMethodNotAllowedResponse(string $method, string $path, array $allowedMethods) : ResponseInterface
    {
        $body = sprintf('Method %s not allowed for %s. Allowed: %s', $method, $path, implode(separator: ', ', array: $allowedMethods));

        return Response::text(content: $body, status: 405)->withHeader(
            name : 'Allow',
            value: implode(separator: ', ', array: $allowedMethods),
        );
    }
}
