<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Connections\Pools;

final class PoolStats
{
    public function __construct(
        public readonly int   $totalConnections,
        public readonly int   $activeConnections,
        public readonly int   $idleConnections,
        public readonly int   $waitingRequests,
        public readonly float $averageWaitTimeMs,
    ) {}
}
