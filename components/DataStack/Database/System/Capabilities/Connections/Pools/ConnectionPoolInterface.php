<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

interface ConnectionPoolInterface
{
    public function get(): PooledConnection;

    public function release(PooledConnection $pooledConnection): void;

    public function destroy(): void;

    public function stats(): PoolStats;
}
