<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Connection\Contracts\ConnectionPoolInterface;
use Exception;

/**
 * Fluent DSL for executing database operations with automatic resource management.
 */
final class DatabaseFlow
{
    /**
     * Target connection identifier for routing queries.
     */
    private string|null $connectionName = null;

    /**
     * Enable connection pooling with acquire/release semantics.
     */
    private bool $pooled = false;

    /**
     * Initialize the fluent query builder with connection manager.
     *
     * @param ConnectionManager $manager Connection lifecycle manager
     */
    public function __construct(private readonly ConnectionManager $manager) {}

    /**
     * Specify the target connection name for query routing.
     *
     * @param string $name Connection identifier
     *
     * @return self Fluent interface for method chaining
     */
    public function on(string $name) : self
    {
        $this->connectionName = $name;

        return $this;
    }

    /**
     * Enable connection pooling with acquire/release lifecycle management.
     *
     * @return self Fluent interface for method chaining
     */
    public function usePool() : self
    {
        $this->pooled = true;

        return $this;
    }

    /**
     * Execute the given logic using the resolved connection with automatic resource cleanup.
     *
     * -- intent: runs the callback with either pooled or direct connection, ensuring
     * proper resource acquisition and release semantics.
     *
     * @param callable $callback Logic to execute with connection instance
     *
     * @return mixed Result of the callback execution
     *
     * @throws Exception Any exception thrown by the callback or connection operations
     */
    public function run(callable $callback) : mixed
    {
        $connection = $this->manager->connection(name: $this->connectionName);

        if ($this->pooled && $connection instanceof ConnectionPoolInterface) {
            $instance = $connection->acquire();
            try {
                return $callback($instance);
            } finally {
                $connection->release(connection: $instance);
            }
        }

        return $callback($connection);
    }
}
