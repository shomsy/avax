<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Exceptions;

use Avax\Database\Exceptions\DatabaseException;

/**
 * A "No More Room" report for a connection pool.
 *
 * -- what is it?
 * This error means the "Shared Library" (Pool) is out of books (Connections).
 * Every single connection is currently being used by someone else, and we've
 * hit the maximum limit you set in the config.
 *
 * -- how to imagine it:
 * Think of a restaurant with only 10 tables. If all 10 tables are full and
 * an 11th customer arrives, the host has to say "Sorry, we're at capacity."
 * This error is that "Sorry" message.
 *
 * -- why this exists:
 * To prevent the server from exploding. If we kept opening new connections
 * forever, the database server would eventually crash from of all the open
 * socket lines. This error provides "Backpressure"—it tells the app to
 * slow down or wait until a connection is returned.
 *
 * -- mental models:
 * - "Saturation": The pool is 100% full.
 * - "Backpressure": Forcing the application to wait or fail early rather
 *    than overwhelming the database.
 */
final class PoolLimitReachedException extends DatabaseException
{
    /**
     * @param  string  $name  The nickname of the pool that is full.
     * @param  int  $limit  The maximum number of people allowed in at once.
     */
    public function __construct(
        private readonly string $name,
        private readonly int $limit
    ) {
        parent::__construct(message: "Connection pool [{$name}] reached its limit of {$limit} connections.");
    }

    /**
     * Get the nickname of the overcrowded pool.
     */
    public function getPoolName(): string
    {
        return $this->name;
    }

    /**
     * Get the maximum capacity of the pool.
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
