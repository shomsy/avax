<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Contracts;

/**
 * Technical contract for implementing connection pooling mechanisms.
 *
 * -- intent: define the standard lifecycle for acquiring and releasing pooled resources.
 */
interface ConnectionPoolInterface
{
    /**
     * Acquire a functional connection from the managed pool.
     *
     * -- intent: retrieve a recycled or fresh connection for immediate use.
     *
     * @return DatabaseConnection
     */
    public function acquire() : DatabaseConnection;

    /**
     * Return a connection instance back to the shared pool.
     *
     * -- intent: signal that the resource is available for other consumers.
     *
     * @param DatabaseConnection $connection The active resource to release
     *
     * @return void
     */
    public function release(DatabaseConnection $connection) : void;
}


