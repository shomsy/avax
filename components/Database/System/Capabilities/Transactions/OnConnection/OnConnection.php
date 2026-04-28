<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Transactions\OnConnection;

use Avax\Components\Database\System\Capabilities\Connections\Connections;
use Avax\Components\Database\System\Capabilities\Transactions\RunTransaction\Transaction;
use Throwable;

/**
 * Resolves a transaction manager for one concrete database connection.
 */
final readonly class OnConnection
{
    public function __construct(private Connections $connections) {}

    /**
     * @throws Throwable
     */
    public function for(string|null $connectionName = null) : Transaction
    {
        return Transaction::on(connection: $this->connections->connection(name: $connectionName));
    }
}
