<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Exceptions\PoolLimitReachedException;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\BuildPhysicalConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\OpenConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\Contracts\ConnectionPoolInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\DTO\ConnectionPoolMetrics;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\ConnectionAcquired;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use ReflectionException;
use SplQueue;
use Throwable;

/**
 * Connection pool implementation for managing a set of reusable database connections.
 *
 * @see /docs/Foundation/Database/Concepts/Connections.md
 */
final class ConnectionPool implements ConnectionPoolInterface
{
    /** @var SplQueue<array{connection: DatabaseConnection, released_at: float}> The "Garage" where idle connections are parked. */
    private SplQueue $pool;

    /** @var PoolState The internal authority who keeps track of how many "Cars" are currently out. */
    private PoolState $state;

    /** @var ExecutionScope|null The "Luggage Tag" (Trace ID) for this pool's actions. */
    private ?ExecutionScope $scope = null;

    private readonly ?EventBus $eventBus;

    private readonly array $config;

    /**
     * @param array<string, mixed> $config   The instructions for the garage (e.g., "Max 10 cars").
     * @param EventBus|null        $eventBus The "Notification System" for reporting when a car is taken or returned.
     */
    public function __construct(
        array $config,
        EventBus $eventBus = null,
    ) {
        $this->config = $config;
        $this->eventBus = $eventBus;
        $this->pool   = new SplQueue();
        $this->state  = new PoolState(
            maxConnections: (int) ($this->config['pool']['max_connections'] ?? 10),
        );
    }

    /**
     * Borrow a healthy connection from the pool.
     *
     * @throws Throwable
     * @throws PoolLimitReachedException If pool capacity is exceeded.
     */
    public function acquire(): DatabaseConnection
    {
        $this->pruneStaleConnections();

        // 1. Try to reuse an existing one.
        if (! $this->pool->isEmpty()) {
            $item = $this->pool->dequeue();
            $connection = $item['connection'];

            if ($this->validateConnection(connection: $connection)) {
                $this->state->recordRecycledAcquisition();

                $this->eventBus?->dispatch(event: new ConnectionAcquired(
                    connectionName: $this->getName(),
                    isRecycled    : true,
                    correlationId : $this->scope?->correlationId ?? 'ctx_unknown',
                ));

                return new BorrowedConnection(connection: $connection, pool: $this);
            }

            // If the connection was dead, we release its slot in our counter.
            $this->state->releaseSlot();
        }

        // 2. If no recyclables, try to create a new one.
        $name = $this->config['name'] ?? 'anonymous';

        if (! $this->state->tryReserveSlot()) {
            $limit = $this->config['pool']['max_connections'] ?? 10;

            throw new PoolLimitReachedException(name: $name, limit: (int) $limit);
        }

        $connection = new OpenConnection(
            buildPhysicalConnection: new BuildPhysicalConnection(),
            eventBus               : $this->eventBus,
            scope                  : $this->scope,
        )->using(config: $this->config);

        $this->eventBus?->dispatch(event: new ConnectionAcquired(
            connectionName: $this->getName(),
            isRecycled    : false,
            correlationId : $this->scope?->correlationId ?? 'ctx_unknown',
        ));

        return new BorrowedConnection(connection: $connection, pool: $this);
    }

    /**
     * Remove stale or closed connections from the pool.
     *
     * @return int Number of pruned connections.
     */
    public function pruneStaleConnections(): int
    {
        $maxIdleTime = $this->config['pool']['max_idle_time_seconds'] ?? 300;
        $currentTime = microtime(as_float: true);
        $prunedCount = 0;

        $validConnections = new SplQueue();

        while (! $this->pool->isEmpty()) {
            $item = $this->pool->dequeue();
            $idleTime = $currentTime - $item['released_at'];

            // If it's too old or doesn't "Ping" correctly, it's gone.
            if ($idleTime > $maxIdleTime || ! $this->validateConnection(connection: $item['connection'])) {
                $this->state->releaseSlot();
                $prunedCount++;

                continue;
            }

            $validConnections->enqueue(value: $item);
        }

        // Put the survivors back in the garage.
        while (! $validConnections->isEmpty()) {
            $this->pool->enqueue(value: $validConnections->dequeue());
        }

        return $prunedCount;
    }

    /**
     * Ask a connection "Are you alive?" (Ping).
     */
    public function validateConnection(DatabaseConnection $connection = null) : bool
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
     * Test the overall health of the pool.
     */
    public function ping(): bool
    {
        try {
            $borrowed = $this->acquire();
            $isHealthy = $borrowed->ping();

            if ($borrowed instanceof BorrowedConnection) {
                $borrowed->release();
            }

            return $isHealthy;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Return a used connection to the pool.
     *
     * @param DatabaseConnection $connection The connection to return.
     */
    public function release(DatabaseConnection $connection): void
    {
        if ($connection instanceof BorrowedConnection) {
            $connection = $connection->getOriginalConnection();
        }

        if (! $this->validateConnection(connection: $connection)) {
            $this->state->releaseSlot();

            return;
        }

        $maxIdle = $this->config['pool']['max_idle_connections'] ?? 5;

        // If the lot is full of idle cars, get rid of the oldest 'parked' one.
        if ($this->pool->count() >= $maxIdle) {
            $this->pool->dequeue();
            $this->state->releaseSlot();
        }

        $this->pool->enqueue(
            value: [
                       'connection' => $connection,
                'released_at' => microtime(as_float: true),
            ],
        );
    }

    /**
     * Get the nickname of this pool.
     */
    public function getName(): string
    {
        return $this->config['name'] ?? 'pool';
    }

    /**
     * Attach a "Luggage Tag" (Scope) to this pool for logging and tracing.
     */
    public function withScope(ExecutionScope $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get a "Status Report" (Metrics) of how the garage is doing.
     *
     * @return ConnectionPoolMetrics A report containing counts of idle/active cars.
     *
     * @throws ReflectionException
     */
    public function getMetrics(): ConnectionPoolMetrics
    {
        $maxIdleTime = 0;
        if (! $this->pool->isEmpty()) {
            $oldest = $this->pool->bottom();
            $maxIdleTime = (int) (microtime(as_float: true) - $oldest['released_at']);
        }

        return new ConnectionPoolMetrics(
            data: [
                'spawnedConnections' => $this->state->spawnedCount,
                'idleConnections'   => $this->pool->count(),
                'activeConnections' => max(0, $this->state->spawnedCount - $this->pool->count()),
                'maxConnections'    => (int) ($this->config['pool']['max_connections'] ?? 10),
                'totalAcquisitions' => $this->state->totalAcquisitions,
                'maxIdleTime'       => $maxIdleTime,
            ],
        );
    }
}
