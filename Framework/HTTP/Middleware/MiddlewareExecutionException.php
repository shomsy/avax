<?php

declare(strict_types=1);

namespace Gemini\HTTP\Middleware;

use Exception;

/**
 * Class MiddlewareExecutionException
 *
 * Exception thrown when a middleware fails during execution.
 *
 * @package Gemini\HTTP\Middleware
 */
final class MiddlewareExecutionException extends Exception
{
    /**
     * MiddlewareExecutionException constructor.
     *
     * @param string         $message  The Exception message to throw.
     * @param int            $code     The Exception code.
     * @param Exception|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(
        string         $message = "",
        int            $code = 0,
        Exception|null $previous = null,
    ) {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }
}
