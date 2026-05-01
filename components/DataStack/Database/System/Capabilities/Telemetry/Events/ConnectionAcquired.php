<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events;

/**
 * Event emitted when a connection is acquired from the pool.
 *
 * @see /docs/Foundation/Database/Concepts/Telemetry.md#connectionacquired
 */
final readonly class ConnectionAcquired extends Event
{
    public bool $isRecycled;

    public string $connectionName;

    /**
     * @param  string  $connectionName  The technical identifier assigned to the target database.
     * @param  bool  $isRecycled  Flag indicating if the connection was retrieved from the pool (true) or freshly
     *                            established (false).
     * @param  string  $correlationId  The technical trace identifier used for correlating this event with a specific
     *                                 execution scope.
     */
    public function __construct(
        string $connectionName,
        bool $isRecycled,
        string $correlationId,
    ) {
        $this->connectionName = $connectionName;
        $this->isRecycled = $isRecycled;
        parent::__construct(correlationId: $correlationId);
    }
}
