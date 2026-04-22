<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections;

use Avax\Database\System\Capabilities\Connections\Pool\Contracts\ConnectionPoolInterface;
use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Throwable;

/**
 * Fluent builder for coordinating one-off database operations with resource safety.
 *
 * @see /docs/Foundation/Database/Concepts/Connections.md
 */
final class DatabaseFlow
{
    /** @var string|null The nickname of the database we want to talk to. */
    private string|null $connectionName = null;

    /** @var bool If true, we will borrow a connection from a shared collection. */
    private bool                       $pooled = false;
    private readonly ConnectionManager $manager;

    /**
     * @param ConnectionManager $manager The "Switchboard Operator" who actually holds the cables.
     */
    public function __construct(ConnectionManager $manager) { $this->manager = $manager; }

    /**
     * Specify the target connection name.
     */
    public function on(string $name) : self
    {
        $this->connectionName = $name;

        return $this;
    }

    /**
     * Enable connection pooling for this flow.
     */
    public function usePool() : self
    {
        $this->pooled = true;

        return $this;
    }

    /**
     * Execute the task with automatic resource management.
     *
     * @param callable(DatabaseConnection): mixed $callback
     *
     * @throws Throwable
     */
    public function run(callable $callback) : mixed
    {
        $connection = $this->manager->connection(name: $this->connectionName);

        // If we requested a pool and the connection supports it...
        if ($this->pooled && $connection instanceof ConnectionPoolInterface) {
            $instance = $connection->acquire();
            try {
                return $callback($instance);
            } finally {
                // This is the "No matter what" cleanup rule.
                $connection->release(connection: $instance);
            }
        }

        // Otherwise, just run the code with the standard connection.
        return $callback($connection);
    }
}
