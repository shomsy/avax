<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Configuration;

final readonly class RealtimeConfiguration
{
    public function __construct(
        public int    $maxConnections = 10000,
        public int    $heartbeatInterval = 30,
        public int    $connectionTimeout = 60,
        public string $driver = 'websocket',
    ) {}
}
