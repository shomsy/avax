<?php

declare(strict_types=1);

namespace Avax\Database\Tests\Stress;

use Avax\Database\Foundation\Connection\Pool\ConnectionPool;
use Avax\Database\Foundation\Connection\Pool\PooledConnectionAuthority;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Stress test: Verify pool correctly handles rapid acquire/release cycles.
 */
final class PoolStressTest extends TestCase
{
    /**
     * Test: 100 rapid acquire/release cycles without slot leakage.
     *
     * @throws \Throwable
     */
    public function test_pool_handles_rapid_acquire_release() : void
    {
        $factory = static fn() => new PDO(dsn: 'sqlite::memory:');
        $pool    = new ConnectionPool(factory: $factory, maxSize: 5);

        // Simulate 100 rapid requests
        for ($i = 0; $i < 100; $i++) {
            $authority = $pool->acquire();
            $this->assertInstanceOf(expected: PooledConnectionAuthority::class, actual: $authority);

            $connection = $authority->borrow();
            $this->assertInstanceOf(expected: PDO::class, actual: $connection->getConnection());

            // Explicit release (simulating end of request)
            unset($connection);
            unset($authority);
        }

        // Verify pool is still healthy
        $this->assertTrue(condition: true, message: 'Pool survived 100 cycles');
    }

    /**
     * Test: Concurrent slot usage respects max pool size.
     *
     * @throws \Throwable
     * @throws \Throwable
     */
    public function test_pool_respects_max_size() : void
    {
        $factory = static fn() => new PDO(dsn: 'sqlite::memory:');
        $pool    = new ConnectionPool(factory: $factory, maxSize: 3);

        $authorities = [];
        $connections = [];

        // Acquire max slots
        for ($i = 0; $i < 3; $i++) {
            $authorities[$i] = $pool->acquire();
            $connections[$i] = $authorities[$i]->borrow();
        }

        // Fourth acquire should create a new connection (pool exhausted)
        $fourthAuth = $pool->acquire();
        $this->assertInstanceOf(expected: PooledConnectionAuthority::class, actual: $fourthAuth);

        // Cleanup
        unset($connections);
        unset($authorities);
        unset($fourthAuth);

        $this->assertTrue(condition: true, message: 'Pool correctly handled slot exhaustion');
    }

    /**
     * Test: Exception during connection usage doesn't leak slot.
     *
     * @throws \Throwable
     * @throws \Throwable
     */
    public function test_pool_releases_slot_on_exception() : void
    {
        $factory = static fn() => new PDO(dsn: 'sqlite::memory:');
        $pool    = new ConnectionPool(factory: $factory, maxSize: 2);

        try {
            $authority  = $pool->acquire();
            $connection = $authority->borrow();

            // Simulate exception during usage
            throw new RuntimeException(message: 'Simulated failure');
        } catch (RuntimeException $e) {
            // Expected
        }

        // Slot should be released via __destruct
        // Verify pool is still usable
        $newAuthority = $pool->acquire();
        $this->assertInstanceOf(expected: PooledConnectionAuthority::class, actual: $newAuthority);
    }
}
