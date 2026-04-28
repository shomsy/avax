<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Connections\Pools;

interface ConnectionPoolInterface
{
    public function get() : PooledConnection;

    public function release(PooledConnection $connection) : void;

    public function destroy() : void;

    public function stats() : PoolStats;
}
