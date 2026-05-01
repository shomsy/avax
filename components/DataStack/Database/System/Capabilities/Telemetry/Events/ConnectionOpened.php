<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events;

/**
 * Event emitted when a fresh database connection is opened.
 *
 * @see /docs/Foundation/Database/Concepts/Telemetry.md#connectionopened
 */
final readonly class ConnectionOpened extends Event
{
    /**
     * @param string $connectionName The technical identifier assigned to the established database channel.
     * @param string $correlationId  The technical trace identifier used for correlating this event with a specific
     *                               execution scope.
     */
    public function __construct(
        public string $connectionName,
        string $correlationId,
    ) {
        parent::__construct(correlationId: $correlationId);
    }
}
