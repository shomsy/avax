<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Validation\Exceptions;

use Exception;

/**
 * Exception thrown when a route constraint pattern is invalid.
 */
class InvalidConstraintException extends Exception
{
    public function __construct(string $pattern, string $reason = '')
    {
        $message = "Invalid route constraint pattern: {$pattern}";
        if (! empty($reason)) {
            $message .= " ({$reason})";
        }

        parent::__construct(message: $message);
    }
}
