<?php

declare(strict_types=1);

namespace Avax\Database\Connection\DTO;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\IntegerRule;

/**
 * Data Transfer Object capturing the instantaneous state of the connection pool.
 */
final class ConnectionPoolMetrics extends AbstractDTO
{
    #[IntegerRule]
    public int $activeConnections;

    #[IntegerRule]
    public int $idleConnections;

    #[IntegerRule]
    public int $maxConnections;

    #[IntegerRule]
    public int $totalAcquisitions;

    #[IntegerRule]
    public int $maxIdleTime;
}
