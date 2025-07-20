<?php

declare(strict_types=1);

namespace Gemini\Database\Migration\Runner\Exception;

use RuntimeException;
use Throwable;

/**
 * MigrationException
 *
 * Represents errors that occur during the migration process.
 * Extends RuntimeException to provide context-specific information for migration failures.
 */
class MigrationException extends RuntimeException
{
    /**
     * Constructor for the MigrationException.
     *
     * @param string         $message  The error message describing the issue.
     * @param int            $code     An optional error code for categorizing the error.
     * @param Throwable|null $previous Optional previous exception for chained exceptions.
     */
    public function __construct(string $message, int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Provides a string representation of the exception for debugging purposes.
     *
     * @return string A detailed message including the exception class and message.
     */
    public function __toString() : string
    {
        return sprintf(
            "[%s]: %s in %s on line %d\nStack trace:\n%s",
            static::class,
            $this->getMessage(),
            $this->getFile(),
            $this->getLine(),
            $this->getTraceAsString()
        );
    }
}
