<?php

declare(strict_types=1);

namespace Gemini\Container\Exceptions;

use Exception;

/**
 * This exception class represents errors specific to the framework's dependency injection container.
 * Extending from the base Exception class provides a standardized way to handle these errors.
 */
class FrameworkContainerException extends Exception
{
    /**
     * Default error message given when no specific message is provided.
     */
    private const string DEFAULT_MESSAGE = 'A container exception has occurred.';

    /**
     * Constructs the FrameworkContainerException.
     *
     * @param string         $message  Custom error message that describes the exception.
     * @param int            $code     Optional error code for the exception.
     * @param Exception|null $previous Optional previous exception for chaining exceptions.
     */
    public function __construct(string $message = self::DEFAULT_MESSAGE, int $code = 0, Exception|null $previous = null)
    {
        // Calling the parent constructor to ensure proper exception handling.
        parent::__construct(message: $message, code: $code, previous: $previous);
    }
}
