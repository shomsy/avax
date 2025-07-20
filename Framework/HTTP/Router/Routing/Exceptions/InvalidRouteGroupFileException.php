<?php

declare(strict_types=1);

namespace Gemini\HTTP\Router\Routing\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Raised when a route group file fails to return a valid RouteGroupBuilder.
 */
final class InvalidRouteGroupFileException extends RuntimeException
{
    public function __construct(string $message, Throwable|null $previous = null)
    {
        parent::__construct($message, previous: $previous);
    }
}
