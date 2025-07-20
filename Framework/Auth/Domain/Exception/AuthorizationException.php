<?php

declare(strict_types=1);

namespace Gemini\Auth\Domain\Exception;

use Exception;

/**
 * AuthorizationException represents an exception thrown during authorization failures.
 *
 * This custom exception is specifically for handling authorization errors within
 * the application, providing a clear message and an appropriate HTTP status code.
 */
class AuthorizationException extends Exception
{
    /**
     * The error message associated with the authorization exception.
     *
     * @var string
     */
    protected $message = 'Authorization failed.';

    /**
     * The HTTP status code used for authorization exceptions.
     *
     * @var int
     */
    protected $code = 403;
}
