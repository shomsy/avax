<?php

declare(strict_types=1);

namespace Avax\Database\Foundation\Connection;

use Avax\Database\Connection\ConnectionFactory;
use Avax\Database\Connection\Contracts\ConnectionPoolInterface;
use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Connection\DTO\ConnectionPoolMetrics;
use Avax\Database\Connection\Exceptions\PoolLimitReachedException;
use PDO;
use ReflectionException;
use SplQueue;
use Throwable;

/**
 * Enterprise-grade technician for managing a pool of database connections.
 *
 * -- intent: optimize resource utilization by recycling active database connections.
 */
final class ConnectionPool implements DatabaseConnection, ConnectionPoolInterface
{
    /** @var SplQueue<array{connection: DatabaseConnection, released_at: float}> */
    private SplQueue $pool;

    // Tracks total number of spawned connections
    private int $activeCount = 0;

    // Metric: Total successful acquisitions
    private int $totalAcquisitions = 0;

    /**
     * Constructor promoting the pool configuration settings.
     *
     * -- intent: initialize pool limitations and heartbeat settings.
     *
     * @param array $config Pool-specific configuration
     */
    public function __construct(private readonly array $config)
    {
        $this->pool = new SplQueue();
    }

    /**
     * Fulfill the DatabaseConnection contract by acquiring a temporary connection.
     *
     * -- intent: allow the pool to be used as a standard connection wrapper.
     *
     * @return PDO
     * @throws PoolLimitReachedException If pool limit is reached
     */
    public function getConnection() : PDO
    {
        return $this->acquire()->getConnection();
    }

    /**
     * Logic to acquire a connection from the idle pool or create a fresh one.
     *
     * -- intent: balance between recycling and on-demand scaling.
     *
     * @return DatabaseConnection
     * @throws PoolLimitReachedException If maximum pool capacity is exceeded
     */
    public function acquire() : DatabaseConnection
    {
        if (! $this->pool->isEmpty()) {
            $item       = $this->pool->dequeue();
            $connection = $item['connection'];

            if ($this->validateConnection(connection: $connection)) {
                $this->totalAcquisitions++;

                return $connection;
            }
            $this->activeCount--;
        }

        $limit = $this->config['pool']['max_connections'] ?? 10;
        $name  = $this->config['name'] ?? 'anonymous';

        if ($this->activeCount >= $limit) {
            throw new PoolLimitReachedException(name: $name, limit: $limit);
        }

        $connection = ConnectionFactory::from(config: $this->config);
        $this->activeCount++;
        $this->totalAcquisitions++;

        return $connection;
    }

    /**
     * Verify the health of a specific pooled connection.
     *
     * -- intent: prevent broken connections from being delivered to the consumer.
     *
     * @param DatabaseConnection|null $connection Target connection
     *
     * @return bool
     */
    public function validateConnection(DatabaseConnection|null $connection = null) : bool
    {
        if ($connection === null) {
            return false;
        }

        try {
            return $connection->ping();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Verify the health of the pool itself.
     *
     * @return bool
     */
    public function ping() : bool
    {
        return true;
    }

    /**
     * Retrieve the logical name/label of the active connection pool.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->config['name'] ?? 'pool';
    }

    /**
     * Retrieve current pool usage metrics.
     *
     * @return ConnectionPoolMetrics
     * @throws ReflectionException
     */
    public function getMetrics() : ConnectionPoolMetrics
    {
        $maxIdleTime = 0;
        if (! $this->pool->isEmpty()) {
            $oldest      = $this->pool->bottom(); // Bottom is oldest in SplQueue (FIFO)
            $maxIdleTime = (int) (microtime(as_float: true) - $oldest['released_at']);
        }

        return new ConnectionPoolMetrics(
            data: [
                      'activeConnections' => $this->activeCount,
                      'idleConnections'   => $this->pool->count(),
                      'maxConnections'    => (int) ($this->config['pool']['max_connections'] ?? 10),
                      'totalAcquisitions' => $this->totalAcquisitions,
                      'maxIdleTime'       => $maxIdleTime
                  ]
        );
    }

    /**
     * Return a connection to the idle pool after usage.
     *
     * -- intent: recycle the resource for future requests.
     *
     * @param DatabaseConnection $connection The connection to release
     *
     * @return void
     */
    public function release(DatabaseConnection $connection) : void
    {
        // ✅ Validation on release
        if (! $this->validateConnection(connection: $connection)) {
            $this->activeCount--;

            return;
        }

        $maxIdle = $this->config['pool']['max_idle_connections'] ?? 5;

        // ✅ Limit queue size and close oldest if full
        if ($this->pool->count() >= $maxIdle) {
            $this->pool->dequeue();
            $this->activeCount--;
        }

        $this->pool->enqueue(
            value: [
                       'connection'  => $connection,
                       'released_at' => microtime(as_float: true)
                   ]
        );
    }
}
