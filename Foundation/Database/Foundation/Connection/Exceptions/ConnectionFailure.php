<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Throwable;

/**
 * Exception thrown when a database connection attempt fails.
 */
class ConnectionFailure extends DatabaseException
{
    public function __construct(
        public readonly string $name,
        string                 $message = "",
        ?Throwable             $previous = null
    )
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }
}


