<?php

declare(strict_types=1);

namespace Avax\Migrations\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Throwable;

/**
 * Triggered during failures in the migration runner or structural execution.
 *
 * -- intent: provide specific diagnostic context for broken schema changes.
 */
final class MigrationException extends DatabaseException
{
    /**
     * Constructor capturing the migration class and technical SQL.
     *
     * -- intent: link the failure to the specific migration file and query.
     *
     * @param string         $migrationClass Technical class name of the migration
     * @param string         $message        Detailed failure description
     * @param string|null    $sql            The specific SQL statement that failed
     * @param Throwable|null $previous       Underlying system trigger
     */
    public function __construct(
        private readonly string      $migrationClass,
        string                       $message,
        private readonly string|null $sql = null,
        Throwable|null               $previous = null
    )
    {
        parent::__construct(message: "Migration [{$migrationClass}] failed: {$message}", code: 0, previous: $previous);
    }

    /**
     * Retrieve the problematic migration's class name.
     *
     * -- intent: identify the broken migration script.
     *
     * @return string
     */
    public function getMigrationClass() : string
    {
        return $this->migrationClass;
    }

    /**
     * Retrieve the SQL statement that caused the structural failure.
     *
     * -- intent: facilitate manual correction of the schema.
     *
     * @return string|null
     */
    public function getSql() : string|null
    {
        return $this->sql;
    }
}


