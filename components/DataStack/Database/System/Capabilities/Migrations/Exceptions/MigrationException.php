<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\Exceptions;

use Avax\Components\DataStack\Database\System\Foundation\Exceptions\DatabaseException;
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
     * @param  string  $migrationClass  Technical class name of the migration
     * @param  string  $message  Detailed failure description
     * @param  string|null  $sql  The specific SQL statement that failed
     * @param  Throwable|null  $throwable  Underlying system trigger
     */
    public function __construct(
        private readonly string $migrationClass,
        string $message,
        private readonly ?string $sql = null, Throwable|null $throwable = null,
    ) {
        parent::__construct(message: sprintf('Migration [%s] failed: %s', $this->migrationClass, $message), code: 0, previous: $throwable);
    }

    /**
     * Retrieve the problematic migration's class name.
     *
     * -- intent: identify the broken migration script.
     */
    public function getMigrationClass(): string
    {
        return $this->migrationClass;
    }

    /**
     * Retrieve the SQL statement that caused the structural failure.
     *
     * -- intent: facilitate manual correction of the schema.
     */
    public function getSql() : string|null
    {
        return $this->sql;
    }
}
