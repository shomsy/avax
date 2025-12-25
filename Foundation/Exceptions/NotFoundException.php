<?php

declare(strict_types=1);

namespace Avax\Exceptions;

use Exception;

/**
 * Class NotFoundException
 *
 * A custom exception class used to indicate that a requested resource could not be found.
 * It extends the built-in Exceptions class and provides a default message and HTTP 404 code.
 *
 * @package Avax\Exceptions
 */
class NotFoundException extends Exception
{
    protected string $defaultMessage = 'Not found!';

    protected        $code           = 404;

    /**
     * Constructor for NotFoundException that appends file, line, and trace information.
     *
     * @param string|null $message Custom message for the exception (optional).
     */
    #[\Override]
    public function __construct(string|null $message = null)
    {
        // Use the custom message if provided, otherwise use the default message.
        $message ??= $this->defaultMessage;

        // Call the parent constructor to initialize the exception with the base message.
        parent::__construct(message: $message, code: $this->code);

        // Now that the exception is initialized, we can append file/line and trace details.
        $detailedMessage = $this->getDetailedMessage();

        // Update the exception's message to include the detailed message.
        $this->message = $detailedMessage;
    }

    /**
     * Get detailed error message with file, line, and stack trace information.
     *
     * @return string The detailed exception message.
     */
    private function getDetailedMessage() : string
    {
        return sprintf(
            "%s in file %s on line %d\nStack trace:\n%s",
            parent::getMessage(),
            $this->getFile(),
            $this->getLine(),
            $this->getTraceAsString(),
        );
    }
}
