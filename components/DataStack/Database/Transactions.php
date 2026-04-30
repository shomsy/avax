<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Transactions\RunTransaction\Transaction;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions as TransactionsCapability;
use Throwable;

final readonly class Transactions
{
    public function __construct(private TransactionsCapability $transactions) {}

    /**
     * @throws Throwable
     */
    public function on(string $connectionName = null) : Transaction
    {
        return $this->transactions->on(connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function run(callable $callback, string $connectionName = null) : mixed
    {
        return $this->transactions->run(callback: $callback, connectionName: $connectionName);
    }
}
