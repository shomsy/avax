<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Throwable;

/**
 * A "Total Connection Failure" report.
 *
 * -- what is it?
 * This is a "Fatal" error. It's more serious than a generic connection 
 * error. It means the database server is completely unreachable or it flat-out 
 * rejected our attempt to talk to it.
 *
 * -- how to imagine it:
 * Think of trying to visit a store and finding the building has burned down 
 * or the doors are welded shut. It's not just a "wrong key" issue; it's a 
 * "the destination doesn't exist or is offline" issue.
 *
 * -- why this exists:
 * To trigger immediate recovery logic. When the system sees this specific 
 * error, it knows there's no point in "trying again" immediately—it might 
 * need to switch to a backup server or show a "Maintenance" page.
 *
 * -- mental models:
 * - "Fatal": The line is permanently dead or blocked.
 * - "Unreachable": We couldn't even find the server on the network.
 */
class ConnectionFailure extends DatabaseException
{
    /**
     * @param string         $name     The nickname of the connection that failed.
     * @param string         $message  The detailed explanation of the failure.
     * @param Throwable|null $previous The raw technical error from the network or driver.
     */
    public function __construct(
        public readonly string $name,
        string                 $message = "",
        Throwable|null $previous = null
    ) {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }
}
