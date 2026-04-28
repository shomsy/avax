<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Foundation\Exceptions;

use Exception;

/**
 * Thrown when a requested resource does not exist.
 * Automatically enriches the message with file, line, and trace context for debugging.
 */
class NotFoundException extends Exception
{
    protected string $defaultMessage = 'Not found!';

    protected $code = 404;

    /**
     * Constructor for NotFoundException that appends file, line, and trace information.
     *
     * @param string|null $message Custom message for the exception (optional).
     */
    public function __construct(string|null $message = null)
    {
        $message ??= $this->defaultMessage;
        parent::__construct(message: $message, code: $this->code);

        $this->message = $this->getDetailedMessage();
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
