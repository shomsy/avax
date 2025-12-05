<?php

declare(strict_types=1);

namespace Gemini\Auth\Exceptions;

use Exception;

/**
 * Custom exception class to handle authentication-related errors.
 *
 * This class extends the base Exception class to provide specific
 * handling for authentication failures within the application.
 *
 * The default message and code properties are overridden to ensure
 * that all instances of this exception carry a consistent error message
 * and HTTP status code (401), indicating unauthorized access.
 */
class AuthFailed extends Exception
{
    /**
     * Default error message for authentication exceptions.
     *
     * This property ensures all instances of this exception have a
     * clear and uniform message that indicates the nature of the error.
     */
    protected $message = 'Authentication failed.';

    /**
     * HTTP status code for unauthorized access.
     *
     * By setting this property to 401, we provide a standard
     * status code that signifies the authentication error to the clients.
     */
    protected $code = 401;
}
