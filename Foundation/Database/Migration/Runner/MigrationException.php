<?php

declare(strict_types=1);

/**
 * Class MigrationException
 *
 * Exceptions class to handle migration-specific errors within the Avax database migration context.
 *
 * This class extends the base Exceptions class to provide custom error handling for
 * database migrations, encapsulating the message, error code, and the previous exception.
 *
 * Example usage:
 * <code>
 * throw new MigrationException("Migration failed due to XYZ reason");
 * </code>
 *
 * @package Avax\Database\Migration
 */

namespace Avax\Database\Migration\Runner;

use Exception;
use Throwable;

/**
 * MigrationException is a custom exception that is thrown during migration operations.
 *
 * The MigrationException class extends the base Exceptions class and provides additional contextual
 * information specifically related to database migration errors. This exception should be used
 * to indicate issues encountered during the process of migrating database schemas or related data.
 *
 * Usage example:
 * throw new MigrationException("Migration failed due to XYZ reason.");
 *
 * @package Avax\Database\Migration
 */
class MigrationException extends Exception
{
    /**
     * Constructs a new MigrationException.
     *
     * @param string          $message  The Exceptions message to throw.
     * @param int             $code     The Exceptions code.
     * @param \Throwable|null $previous The previous throwable used for the exception chaining.
     */
    public function __construct(
        string         $message = '',
        int            $code = 0,
        Throwable|null $previous = null,
    ) {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }
}
