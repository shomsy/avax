<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

final readonly class PoolStats
{
    public function __construct(
        public int   $totalConnections,
        public int   $activeConnections,
        public int   $idleConnections,
        public int   $waitingRequests,
        public float $averageWaitTimeMs,
    ) {}
}
