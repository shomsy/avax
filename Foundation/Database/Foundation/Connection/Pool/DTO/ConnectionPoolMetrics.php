<?php

declare(strict_types=1);

namespace Avax\Database\Foundation\Connection\Pool\DTO;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\IntegerRule;

/**
 * The "Status Report" (Snapshot) for a connection pool.
 *
 * -- what is it?
 * This is a DTO (Data Transfer Object). It's a simple, typed container used
 * to carry a "Snapshot" of how the connection pool is doing right now.
 *
 * -- how to imagine it:
 * Think of a "Dashboard" in a car. It doesn't drive the car; it just
 * shows you the speed, fuel level, and oil pressure. This class shows
 * you the "Fuel level" (how many connections are left) and "Speed" (how
 * many times people have used it) of the pool.
 *
 * -- why this exists:
 * To provide a clean, standard way to look into the "Black Box" of
 * the connection pool. If you're building a monitoring page or trying
 * to debug why the app is slow, this report tells you if the pool is
 * overcrowded or empty.
 *
 * -- mental models:
 * - "Spawned": Connections that have been created and are currently alive.
 * - "Active": Connections that someone is currently using.
 * - "Idle": Connections sitting in the garage waiting for work.
 */
final class ConnectionPoolMetrics extends AbstractDTO
{
    /** @var int The total number of connections that exist right now. */
    #[IntegerRule]
    public int $spawnedConnections;

    /** @var int How many connections are busy right now. */
    #[IntegerRule]
    public int $activeConnections;

    /** @var int How many connections are waiting for work in the garage. */
    #[IntegerRule]
    public int $idleConnections;

    /** @var int The absolute maximum number of connections we are allowed to open. */
    #[IntegerRule]
    public int $maxConnections;

    /** @var int A historical counter of how many times any connection was borrowed since the start. */
    #[IntegerRule]
    public int $totalAcquisitions;

    /** @var int The longest time a connection is allowed to sit idle before we "prune" it (throw it away). */
    #[IntegerRule]
    public int $maxIdleTime;
}
