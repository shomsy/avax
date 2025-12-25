<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Override;
use Throwable;

/**
 * General connection error.
 */
class ConnectionException extends DatabaseException
{
    /**
     * Constructor capturing the connection name and failure details.
     *
     * -- intent: provide visibility into which connection configuration failed.
     *
     * @param string         $name     Technical connection identifier
     * @param string         $message  Driver-provided error message
     * @param Throwable|null $previous Original PDO or socket exception
     */
    #[Override]
    public function __construct(
        private readonly string $name,
        string                  $message,
        Throwable|null          $previous = null
    )
    {
        parent::__construct(message: "Connection [{$name}] failed: {$message}", code: 0, previous: $previous);
    }

    /**
     * Retrieve the technical name of the failing connection.
     *
     * -- intent: allow identification of the problematic database node.
     *
     * @return string
     */
    public function getConnectionName() : string
    {
        return $this->name;
    }
}


