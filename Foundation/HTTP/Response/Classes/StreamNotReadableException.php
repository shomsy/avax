<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Classes;

use RuntimeException;

/**
 * Exception thrown when a stream is not readable.
 *
 * Extends RuntimeException to indicate an unexpected error occurred
 * due to a non-readable stream within the Avax HTTP response process.
 */
class StreamNotReadableException extends RuntimeException
{
    /**
     * Constructs a new StreamNotReadableException with a specific message.
     *
     * @param string $message Descriptive message explaining why the stream is not readable.
     */
    public function __construct(string $message)
    {
        // Pass the message to the RuntimeException constructor.
        parent::__construct(message: $message);
    }
}
