<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Flows\ConnectClient;

use Avax\Components\Operations\Realtime\System\Capabilities\Connections\Connection;
use Avax\Components\Operations\Realtime\System\Capabilities\Connections\ConnectionPool;
use Closure;

final readonly class ConnectClient
{
    public function connect(ConnectionPool $pool, Closure|null $sendCallback = null, string|null $id = null) : Connection
    {
        $connection = new Connection(
            sender: $sendCallback ?? static fn (mixed $_message) : mixed => null,
            id    : $id,
        );
        $pool->add($connection);

        return $connection;
    }
}
