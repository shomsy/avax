<?php

declare(strict_types=1);

namespace Avax\Database\Events;

/**
 * Event emitted when a connection is acquired from the pool.
 *
 * @see docs/Concepts/Telemetry.md
 */
final readonly class ConnectionAcquired extends Event
{
    /**
     * @param string $connectionName The technical identifier assigned to the target database.
     * @param bool   $isRecycled     Flag indicating if the connection was retrieved from the pool (true) or freshly established (false).
     * @param string $correlationId  The technical trace identifier used for correlating this event with a specific execution scope.
     */
    public function __construct(
        public string $connectionName,
        public bool   $isRecycled,
        string        $correlationId
    ) {
        parent::__construct(correlationId: $correlationId);
    }
}
