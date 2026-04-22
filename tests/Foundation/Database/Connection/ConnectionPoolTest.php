<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Database\Connection;

use Avax\Database\System\Capabilities\Connections\Pools\ConnectionPool;
use PHPUnit\Framework\TestCase;

class ConnectionPoolTest extends TestCase
{
    public function test_connection_pool_initialization() : void
    {
        $pool = new ConnectionPool(
            config: [
                        'name'     => 'sqlite_pool',
                        'driver'   => 'sqlite',
                        'database' => ':memory:',
                        'pool'     => [
                            'max_connections'      => 2,
                            'max_idle_connections' => 1,
                        ],
                    ]
        );

        $this->assertInstanceOf(expected: ConnectionPool::class, actual: $pool);
        $this->assertSame(expected: 'sqlite_pool', actual: $pool->getName());
    }

    public function test_prune_stale_connections() : void
    {
        $pool = new ConnectionPool(config: [
                                               'name' => 'sqlite_pool',
                                               'pool' => [
                                                   'max_connections'       => 1,
                                                   'max_idle_connections'  => 1,
                                                   'max_idle_time_seconds' => 1,
                                               ],
                                           ]);

        $this->assertSame(expected: 0, actual: $pool->pruneStaleConnections());
    }
}
