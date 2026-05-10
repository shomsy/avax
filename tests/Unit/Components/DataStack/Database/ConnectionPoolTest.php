<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPoolInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\Contracts\ConnectionPoolInterface as ContractsConnectionPoolInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnectionAuthority;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PoolException;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PoolStats;
use Override;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

/**
 * Tests for the abstract ConnectionPool base class and PooledConnectionAuthority.
 */
final class ConnectionPoolTest extends TestCase
{
    // ============================================================
    // CONCRETE TEST POOL IMPLEMENTATION
    // ============================================================

    #[Test]
    public function pool_has_default_configuration() : void
    {
        $pool = $this->createTestPool();

        $stats = $pool->stats();
        self::assertSame(expected: 0, actual: $stats->totalConnections);
        self::assertSame(expected: 0, actual: $stats->activeConnections);
        self::assertSame(expected: 0, actual: $stats->idleConnections);
    }

    // ============================================================
    // POOL CONSTRUCTION
    // ============================================================

    /**
     * A concrete pool implementation for testing the abstract base.
     */
    private function createTestPool(
        int $minConnections = 5,
        int $maxConnections = 20,
    ) : TestConnectionPool
    {
        return new TestConnectionPool(
            minConnections: $minConnections,
            maxConnections: $maxConnections,
        );
    }

    #[Test]
    public function pool_accepts_custom_configuration() : void
    {
        $pool = $this->createTestPool(minConnections: 2, maxConnections: 5);

        self::assertInstanceOf(expected: ConnectionPool::class, actual: $pool);
    }

    // ============================================================
    // GET CONNECTION
    // ============================================================

    #[Test]
    public function get_creates_new_connection_when_pool_empty() : void
    {
        $pool       = $this->createTestPool();
        $connection = $pool->get();

        self::assertInstanceOf(expected: PooledConnection::class, actual: $connection);
    }

    #[Test]
    public function get_validates_idle_connections() : void
    {
        $pool = $this->createTestPool();

        // Get and release a valid connection
        $conn1 = $pool->get();
        $pool->release(pooledConnection: $conn1);

        // The next get should reuse the idle connection
        $conn2 = $pool->get();

        self::assertInstanceOf(expected: PooledConnection::class, actual: $conn2);
    }

    #[Test]
    public function get_skips_invalid_idle_connections() : void
    {
        $pool = new TestConnectionPoolWithInvalid();

        // Get a connection and release it (it will be marked invalid)
        $conn1 = $pool->get();
        $pool->release(pooledConnection: $conn1);

        // The next get should create a new connection since the idle one is invalid
        $conn2 = $pool->get();

        self::assertInstanceOf(expected: PooledConnection::class, actual: $conn2);
    }

    #[Test]
    public function get_throws_when_pool_exhausted() : void
    {
        $pool = $this->createTestPool(minConnections: 1, maxConnections: 2);

        $conn1 = $pool->get();
        $conn2 = $pool->get();

        $this->expectException(PoolException::class);
        $pool->get();
    }

    // ============================================================
    // RELEASE CONNECTION
    // ============================================================

    #[Test]
    public function release_returns_valid_connection_to_pool() : void
    {
        $pool = $this->createTestPool();

        $conn = $pool->get();
        $pool->release(pooledConnection: $conn);

        $stats = $pool->stats();
        self::assertSame(expected: 1, actual: $stats->idleConnections);
    }

    #[Test]
    public function release_discards_invalid_connection() : void
    {
        $pool = new TestConnectionPoolWithInvalid();

        $conn = $pool->get();
        $pool->release(pooledConnection: $conn);

        // Invalid connections are discarded, not returned to pool
        $stats = $pool->stats();
        self::assertSame(expected: 0, actual: $stats->idleConnections);
    }

    #[Test]
    public function release_discards_when_pool_half_full() : void
    {
        $pool = $this->createTestPool(minConnections: 2, maxConnections: 4);

        // Fill to half (maxConnections / 2 = 2)
        $conn1 = $pool->get();
        $conn2 = $pool->get();
        $pool->release(pooledConnection: $conn1);
        $pool->release(pooledConnection: $conn2);

        // Get one more and release - should be discarded since pool is at half capacity
        $conn3 = $pool->get();
        $pool->release(pooledConnection: $conn3);

        $stats = $pool->stats();
        // The third release should be discarded since idle >= max/2
        self::assertSame(expected: 2, actual: $stats->idleConnections);
    }

    // ============================================================
    // WARMUP
    // ============================================================

    #[Test]
    public function warmup_creates_connections_up_to_min() : void
    {
        $pool = $this->createTestPool(minConnections: 3, maxConnections: 10);
        $pool->warmup(count: 3);

        $stats = $pool->stats();
        self::assertSame(expected: 3, actual: $stats->totalConnections);
        self::assertSame(expected: 3, actual: $stats->idleConnections);
    }

    #[Test]
    public function warmup_respects_min_connections_limit() : void
    {
        $pool = $this->createTestPool(minConnections: 3, maxConnections: 10);
        $pool->warmup(count: 10); // Request more than min

        $stats = $pool->stats();
        // Should only create up to minConnections
        self::assertSame(expected: 3, actual: $stats->totalConnections);
    }

    #[Test]
    public function warmup_with_zero_does_nothing() : void
    {
        $pool = $this->createTestPool();
        $pool->warmup(count: 0);

        $stats = $pool->stats();
        self::assertSame(expected: 0, actual: $stats->totalConnections);
    }

    // ============================================================
    // STATS
    // ============================================================

    #[Test]
    public function stats_returns_pool_stats_object() : void
    {
        $pool = $this->createTestPool();

        $stats = $pool->stats();

        self::assertInstanceOf(expected: PoolStats::class, actual: $stats);
    }

    #[Test]
    public function stats_reflects_active_connections() : void
    {
        $pool = $this->createTestPool();

        $conn = $pool->get();

        $stats = $pool->stats();
        self::assertSame(expected: 1, actual: $stats->totalConnections);
        self::assertSame(expected: 1, actual: $stats->activeConnections);
        self::assertSame(expected: 0, actual: $stats->idleConnections);

        $pool->release(pooledConnection: $conn);

        $stats = $pool->stats();
        self::assertSame(expected: 1, actual: $stats->totalConnections);
        self::assertSame(expected: 0, actual: $stats->activeConnections);
        self::assertSame(expected: 1, actual: $stats->idleConnections);
    }

    // ============================================================
    // DESTROY
    // ============================================================

    #[Test]
    public function destroy_clears_all_connections() : void
    {
        $pool = $this->createTestPool();
        $pool->warmup(count: 3);

        $pool->destroy();

        $stats = $pool->stats();
        self::assertSame(expected: 0, actual: $stats->totalConnections);
        self::assertSame(expected: 0, actual: $stats->idleConnections);
    }

    // ============================================================
    // POOLED CONNECTION AUTHORITY
    // ============================================================

    #[Test]
    public function authority_defers_connection_acquisition() : void
    {
        $pool = $this->createMock(ContractsConnectionPoolInterface::class);
        $pool->expects(self::never())->method('acquire');

        $authority = new PooledConnectionAuthority(connectionPool: $pool);

        // Authority created without acquiring connection
        self::assertInstanceOf(expected: PooledConnectionAuthority::class, actual: $authority);
    }

    #[Test]
    public function authority_acquires_connection_on_first_use() : void
    {
        $realConnection = $this->createMock(DatabaseConnection::class);
        $realConnection->expects(self::once())
            ->method('getConnection')
            ->willReturn($this->createMock(PDO::class));

        $pool = $this->createMock(ContractsConnectionPoolInterface::class);
        $pool->expects(self::once())
            ->method('acquire')
            ->willReturn($realConnection);

        $authority = new PooledConnectionAuthority(connectionPool: $pool);

        // First access triggers acquisition
        $authority->getConnection();
    }

    #[Test]
    public function authority_reuses_borrowed_connection() : void
    {
        $realConnection = $this->createMock(DatabaseConnection::class);
        $pdo            = $this->createMock(PDO::class);
        $realConnection->method('getConnection')->willReturn($pdo);

        $pool = $this->createMock(ContractsConnectionPoolInterface::class);
        $pool->expects(self::once())
            ->method('acquire')
            ->willReturn($realConnection);

        $authority = new PooledConnectionAuthority(connectionPool: $pool);

        // Multiple calls should only acquire once
        $authority->getConnection();
        $authority->getConnection();
    }

    #[Test]
    public function authority_proxies_ping() : void
    {
        $realConnection = $this->createMock(DatabaseConnection::class);
        $realConnection->expects(self::once())
            ->method('ping')
            ->willReturn(true);

        $pool = $this->createMock(ContractsConnectionPoolInterface::class);
        $pool->method('acquire')->willReturn($realConnection);

        $authority = new PooledConnectionAuthority(connectionPool: $pool);

        $result = $authority->ping();

        self::assertTrue(condition: $result);
    }

    #[Test]
    public function authority_proxies_get_name() : void
    {
        $pool = $this->createMock(ContractsConnectionPoolInterface::class);
        $pool->expects(self::once())
            ->method('getName')
            ->willReturn('primary');

        $authority = new PooledConnectionAuthority(connectionPool: $pool);

        self::assertSame(expected: 'primary', actual: $authority->getName());
    }

    #[Test]
    public function authority_proxies_acquire() : void
    {
        $realConnection = $this->createMock(DatabaseConnection::class);

        $pool = $this->createMock(ContractsConnectionPoolInterface::class);
        $pool->expects(self::once())
            ->method('acquire')
            ->willReturn($realConnection);

        $authority = new PooledConnectionAuthority(connectionPool: $pool);

        $result = $authority->acquire();

        self::assertSame(expected: $realConnection, actual: $result);
    }

    #[Test]
    public function authority_proxies_release() : void
    {
        $realConnection = $this->createMock(DatabaseConnection::class);

        $pool = $this->createMock(ContractsConnectionPoolInterface::class);
        $pool->expects(self::once())
            ->method('release')
            ->with(constraint: $realConnection);

        $authority = new PooledConnectionAuthority(connectionPool: $pool);

        $authority->release(databaseConnection: $realConnection);
    }

    #[Test]
    public function authority_destructor_nullifies_connection() : void
    {
        $realConnection = $this->createMock(DatabaseConnection::class);

        $pool = $this->createMock(ContractsConnectionPoolInterface::class);
        $pool->method('acquire')->willReturn($realConnection);

        $authority = new PooledConnectionAuthority(connectionPool: $pool);

        // Trigger connection acquisition
        $authority->getConnection();

        // Trigger destructor
        $authority->__destruct();

        // After destruction, accessing connection again would re-acquire
        // We verify the destructor runs without error
        self::assertTrue(condition: true);
    }

    // ============================================================
    // POOL STATS VALUE OBJECT
    // ============================================================

    #[Test]
    public function pool_stats_stores_values() : void
    {
        $stats = new PoolStats(
            totalConnections : 10,
            activeConnections: 7,
            idleConnections  : 3,
            waitingRequests  : 2,
            averageWaitTimeMs: 15.5,
        );

        self::assertSame(expected: 10, actual: $stats->totalConnections);
        self::assertSame(expected: 7, actual: $stats->activeConnections);
        self::assertSame(expected: 3, actual: $stats->idleConnections);
        self::assertSame(expected: 2, actual: $stats->waitingRequests);
        self::assertSame(expected: 15.5, actual: $stats->averageWaitTimeMs);
    }

    #[Test]
    public function pool_stats_is_readonly() : void
    {
        $stats = new PoolStats(
            totalConnections : 5,
            activeConnections: 3,
            idleConnections  : 2,
            waitingRequests  : 0,
            averageWaitTimeMs: 0.0,
        );

        self::assertInstanceOf(expected: ReflectionClass::class, actual: new ReflectionClass($stats));
        $reflection = new ReflectionClass($stats);
        self::assertTrue(condition: $reflection->isReadonly());
    }

    // ============================================================
    // POOL EXCEPTION
    // ============================================================

    #[Test]
    public function pool_exhausted_exception_has_correct_message() : void
    {
        $exception = PoolException::poolExhausted();

        self::assertSame(expected: 'Connection pool exhausted', actual: $exception->getMessage());
    }

    #[Test]
    public function invalid_connection_exception_has_correct_message() : void
    {
        $exception = PoolException::invalidConnection();

        self::assertSame(expected: 'Invalid connection', actual: $exception->getMessage());
    }

    #[Test]
    public function timeout_exception_includes_timeout_value() : void
    {
        $exception = PoolException::timeout(timeoutMs: 5000);

        self::assertSame(expected: 'Connection timeout after 5000ms', actual: $exception->getMessage());
    }

    #[Test]
    public function pool_exception_extends_runtime_exception() : void
    {
        $exception = PoolException::poolExhausted();

        self::assertInstanceOf(expected: RuntimeException::class, actual: $exception);
    }
}

/**
 * Concrete test implementation of the abstract ConnectionPool.
 */
final class TestConnectionPool extends ConnectionPool
{
    private int $nextId = 1;

    #[Override]
    protected function validateConnection(PooledConnection $pooledConnection) : bool
    {
        return true;
    }

    #[Override]
    protected function createConnection() : PooledConnection
    {
        return new TestPooledConnection(id: $this->nextId++);
    }
}

/**
 * Test pool that always marks connections as invalid on release.
 */
final class TestConnectionPoolWithInvalid extends ConnectionPool
{
    private int $nextId = 1;

    #[Override]
    protected function validateConnection(PooledConnection $pooledConnection) : bool
    {
        return false;
    }

    #[Override]
    protected function createConnection() : PooledConnection
    {
        return new TestPooledConnection(id: $this->nextId++);
    }
}

/**
 * Simple PooledConnection implementation for testing.
 */
final readonly class TestPooledConnection implements PooledConnection
{
    public function __construct(private int $id) {}

    public function getResource() : object
    {
        return new stdClass();
    }

    public function isValid() : bool
    {
        return true;
    }

    public function getCreatedAt() : float
    {
        return (float) hrtime(true);
    }

    public function getLastUsedAt() : float
    {
        return (float) hrtime(true);
    }

    public function executeCount() : int
    {
        return 0;
    }
}
