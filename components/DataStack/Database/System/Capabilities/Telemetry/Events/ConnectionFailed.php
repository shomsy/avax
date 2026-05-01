<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events;

use Throwable;

/**
 * Event emitted when a database connection attempt fails.
 *
 * @see /docs/Foundation/Database/Concepts/Telemetry.md#connectionfailed
 */
final readonly class ConnectionFailed extends Event
{
    public Throwable $exception;

    public string $connectionName;

    /**
     * @param string    $connectionName The technical identifier of the database gateway that failed to respond.
     * @param Throwable $exception      The native driver exception or technical error captured during the attempt.
     * @param string    $correlationId  The technical trace identifier used for correlating this failure with a
     *                                  specific execution scope.
     */
    public function __construct(
        string $connectionName,
        Throwable $exception,
        string $correlationId,
    ) {
        $this->connectionName = $connectionName;
        $this->exception = $exception;
        parent::__construct(correlationId: $correlationId);
    }
}
