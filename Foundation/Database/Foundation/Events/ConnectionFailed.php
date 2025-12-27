<?php

declare(strict_types=1);

namespace Avax\Database\Events;

use Throwable;

/**
 * Event emitted when a database connection attempt fails.
 *
 * @see docs/Concepts/Telemetry.md
 */
final readonly class ConnectionFailed extends Event
{
    /**
     * @param string    $connectionName The technical identifier of the database gateway that failed to respond.
     * @param Throwable $exception      The native driver exception or technical error captured during the attempt.
     * @param string    $correlationId  The technical trace identifier used for correlating this failure with a specific execution scope.
     */
    public function __construct(
        public string    $connectionName,
        public Throwable $exception,
        string           $correlationId
    ) {
        parent::__construct(correlationId: $correlationId);
    }
}
