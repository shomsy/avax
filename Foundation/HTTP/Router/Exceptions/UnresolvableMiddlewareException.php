<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Exceptions;

use RuntimeException;

/**
 * This exception is thrown when middleware cannot be resolved.
 *
 * @package Avax\HTTP\Router\Exceptions
 *
 * @see     RuntimeException
 *
 * Typical use case:
 * - This exception may be thrown during runtime when middleware identified
 *   by name, configuration, or parameters cannot be instantiated or located.
 *
 * Design Considerations:
 * - This is part of the overall exception hierarchy, promoting better error
 *   differentiation and allowing targeted exception handling.
 */
class UnresolvableMiddlewareException extends RuntimeException
{
    // This class does not currently declare any properties or methods of its own.
    // It serves as a lightweight way to provide a specific exception type
    // for middleware resolution issues while retaining other features of RuntimeException.
}