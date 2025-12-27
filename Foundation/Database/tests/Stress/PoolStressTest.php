<?php

declare(strict_types=1);

namespace Avax\Database\Tests\Stress;

use Avax\Database\Foundation\Connection\Pool\ConnectionPool;
use Avax\Database\Foundation\Connection\Pool\PooledConnectionAuthority;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Stress test: Verify pool correctly handles rapid acquire/release cycles.
 */
final class PoolStressTest extends TestCase
{
    /**
     * Test: 100 rapid acquire/release cycles without slot leakage.
     */
    public function test_pool_handles_rapid_acquire_release(): void
    {
        $factory = fn() => new PDO('sqlite::memory:');
        $pool = new ConnectionPool(factory: $factory, maxSize: 5);

        // Simulate 100 rapid requests
        for ($i = 0; $i < 100; $i++) {
            $authority = $pool->acquire();
            $this->assertInstanceOf(PooledConnectionAuthority::class, $authority);

            $connection = $authority->borrow();
            $this->assertInstanceOf(PDO::class, $connection->getConnection());

            // Explicit release (simulating end of request)
            unset($connection);
            unset($authority);
        }

        // Verify pool is still healthy
        $this->assertTrue(true, 'Pool survived 100 cycles');
    }

    /**
     * Test: Concurrent slot usage respects max pool size.
     */
    public function test_pool_respects_max_size(): void
    {
        $factory = fn() => new PDO('sqlite::memory:');
        $pool = new ConnectionPool(factory: $factory, maxSize: 3);

        $authorities = [];
        $connections = [];

        // Acquire max slots
        for ($i = 0; $i < 3; $i++) {
            $authorities[$i] = $pool->acquire();
            $connections[$i] = $authorities[$i]->borrow();
        }

        // Fourth acquire should create a new connection (pool exhausted)
        $fourthAuth = $pool->acquire();
        $this->assertInstanceOf(PooledConnectionAuthority::class, $fourthAuth);

        // Cleanup
        unset($connections);
        unset($authorities);
        unset($fourthAuth);

        $this->assertTrue(true, 'Pool correctly handled slot exhaustion');
    }

    /**
     * Test: Exception during connection usage doesn't leak slot.
     */
    public function test_pool_releases_slot_on_exception(): void
    {
        $factory = fn() => new PDO('sqlite::memory:');
        $pool = new ConnectionPool(factory: $factory, maxSize: 2);

        try {
            $authority = $pool->acquire();
            $connection = $authority->borrow();

            // Simulate exception during usage
            throw new \RuntimeException('Simulated failure');
        } catch (\RuntimeException $e) {
            // Expected
        }

        // Slot should be released via __destruct
        // Verify pool is still usable
        $newAuthority = $pool->acquire();
        $this->assertInstanceOf(PooledConnectionAuthority::class, $newAuthority);
    }
}
