<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Flows\DisconnectClient;

use Avax\Components\Operations\Realtime\System\Capabilities\Connections\ConnectionPool;

final readonly class DisconnectClient
{
    public function disconnect(ConnectionPool $pool, string $connectionId) : void
    {
        $pool->remove($connectionId);
    }
}
