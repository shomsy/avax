<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Exceptions\PoolLimitReachedException;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\DatabaseConnectionPool;
use PHPUnit\Framework\TestCase;

final class DatabaseCapabilitiesTest extends TestCase
{
    public function test_pool_limit_reached_behavior() : void
    {
        $config = [
            'name'     => 'test_pool',
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'pool'     => ['max_connections' => 1]
        ];

        $pool = new DatabaseConnectionPool($config);

        // First acquisition should succeed
        $conn1 = $pool->acquire();
        $this->assertInstanceOf(DatabaseConnection::class, $conn1);

        // Second acquisition should fail as limit is 1
        $this->expectException(PoolLimitReachedException::class);
        $this->expectExceptionMessage('Connection pool [test_pool] reached its limit of 1 connections.');

        $pool->acquire();
    }

    public function test_pool_metrics_reporting() : void
    {
        $config = [
            'name'     => 'metrics_pool',
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'pool'     => ['max_connections' => 10]
        ];

        $pool = new DatabaseConnectionPool($config);
        $conn = $pool->acquire();

        $metrics = $pool->getMetrics();

        $this->assertSame(1, $metrics->spawnedConnections);
        $this->assertSame(0, $metrics->idleConnections);
        $this->assertSame(1, $metrics->activeConnections);
        $this->assertSame(10, $metrics->maxConnections);
    }
}
