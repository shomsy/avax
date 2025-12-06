<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Exceptions;

use RuntimeException;

/**
 * Thrown when a route explicitly defines an authorization requirement
 * that the request does not fulfill.
 */
final class UnauthorizedException extends RuntimeException
{
    public static function because(string $reason = 'Access denied.') : self
    {
        return new self($reason);
    }
}
