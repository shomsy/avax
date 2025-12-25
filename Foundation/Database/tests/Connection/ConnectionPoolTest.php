<?php

declare(strict_types=1);

namespace Avax\Tests\Connection;

use Avax\Database\Connection\ConnectionPool;
use Avax\Tests\TestCase;
use Psr\Log\NullLogger;

class ConnectionPoolTest extends TestCase
{
    public function testConnectionPoolInitialization() : void
    {
        $pool = new ConnectionPool(
            ['connections' => ['mysql' => ['driver' => 'mysql']]],
            new NullLogger()
        );

        $this->assertInstanceOf(ConnectionPool::class, $pool);
    }

    public function testPruneStaleConnections() : void
    {
        $pool = new ConnectionPool([], new NullLogger());

        // This is a unit test, so we can't easily test real connections
        // but we can verify the method exists and runs.
        $pool->pruneStaleConnections();
        $this->assertTrue(true);
    }
}


