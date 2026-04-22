<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Transactions;

use Avax\Database\System\Capabilities\Connections\Connections;
use Throwable;

/**
 * Public capability owner for transaction boundaries.
 */
final readonly class Transactions
{
    public function __construct(private Connections $connections) {}

    /**
     * @throws Throwable
     */
    public function on(string|null $connectionName = null) : Transaction
    {
        return Transaction::on(connection: $this->connections->connection(name: $connectionName));
    }

    /**
     * @throws Throwable
     */
    public function run(callable $callback, string|null $connectionName = null) : mixed
    {
        return $this->on(connectionName: $connectionName)->transaction(callback: $callback);
    }
}
