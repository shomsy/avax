<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Flows\RunRoute\Responses;

use Avax\Components\HTTP\Response\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Factory for creating standard error responses.
 */
final readonly class ErrorResponseFactory
{
    public static function create(int $status, string $message = '') : ResponseInterface
    {
        return Response::text($message ?: self::getDefaultMessage($status), $status);
    }

    private static function getDefaultMessage(int $status) : string
    {
        return match ($status) {
            404     => 'Route not found',
            405     => 'Method not allowed',
            default => 'Internal Server Error',
        };
    }
}
