<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\OnConnection\OnConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\RunTransaction\RunTransaction;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\RunTransaction\Transaction;
use Throwable;

/**
 * Public capability owner for transaction boundaries.
 */
final readonly class Transactions
{
    private OnConnection $onConnection;
    private RunTransaction $runTransaction;

    public function __construct(
        private Connections $connections,
        OnConnection   $onConnection = null,
        RunTransaction $runTransaction = null,
    )
    {
        $this->onConnection = $onConnection ?? new OnConnection(connections: $this->connections);
        $this->runTransaction = $runTransaction ?? new RunTransaction(onConnection: $this->onConnection);
    }

    /**
     * @throws Throwable
     */
    public function on(string $connectionName = null) : Transaction
    {
        return $this->onConnection->for(connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function run(callable $callback, string $connectionName = null) : mixed
    {
        return $this->runTransaction->run(callback: $callback, connectionName: $connectionName);
    }
}
