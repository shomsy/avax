<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Override;
use Throwable;

/**
 * A "Connection Error" report.
 *
 * -- what is it?
 * This is a specialized error (Exception). It's what the system "screams" 
 * when it tries to talk to a database but something goes wrong with 
 * the line itself (e.g., wrong password, server is offline).
 *
 * -- how to imagine it:
 * Think of it as a "Service Ticket". It's not just a generic "Something 
 * broke" message; it's a specific report that says "I tried to call the 
 * 'Primary' database, but the line was busy/dead."
 *
 * -- why this exists:
 * To make debugging easier. Instead of just seeing a raw computer error, 
 * this object carries the "Nickname" of the connection that failed, so 
 * you know exactly which server to check.
 *
 * -- mental models:
 * - "Immutable": Once this error is created, you can't change its details. 
 *    It's a permanent record of what happened at that moment.
 */
class ConnectionException extends DatabaseException
{
    /**
     * @param string         $name     The nickname of the database connection that failed.
     * @param string         $message  The human-readable description of what went wrong.
     * @param Throwable|null $previous The raw system error that triggered this report.
     */
    #[Override]
    public function __construct(
        private readonly string $name,
        string                  $message,
        Throwable|null          $previous = null
    ) {
        parent::__construct(message: "Connection [{$name}] failed: {$message}", code: 0, previous: $previous);
    }

    /**
     * Get the nickname of the failing database.
     *
     * @return string The nickname (e.g., 'primary').
     */
    public function getConnectionName(): string
    {
        return $this->name;
    }
}
