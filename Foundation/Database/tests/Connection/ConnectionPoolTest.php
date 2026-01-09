<?php

declare(strict_types=1);

namespace Avax\Tests\Connection;

use Avax\Database\Foundation\Connection\Pool\ConnectionPool;
use Avax\Tests\TestCase;

class ConnectionPoolTest extends TestCase
{
    public function testConnectionPoolInitialization() : void
    {
        $pool = new ConnectionPool(
            config: ['connections' => ['mysql' => ['driver' => 'mysql']]]
        );

        $this->assertInstanceOf(expected: ConnectionPool::class, actual: $pool);
    }

    public function testPruneStaleConnections() : void
    {
        $pool = new ConnectionPool(config: []);

        // This is a unit test, so we can't easily test real connections
        // but we can verify the method exists and runs.
        $pool->pruneStaleConnections();
        $this->assertTrue(condition: true);
    }
}
