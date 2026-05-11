<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions as TransactionsCapability;

/**
 * Public surface for Database Transactions.
 */
final readonly class Transactions
{
    public function __construct(
        private TransactionsCapability $transactionsCapability,
    ) {
    }

    public function begin(string|null $connectionName = null) : void
    {
        $this->transactionsCapability->begin($connectionName);
    }

    public function commit(string|null $connectionName = null) : void
    {
        $this->transactionsCapability->commit($connectionName);
    }

    public function rollback(string|null $connectionName = null) : void
    {
        $this->transactionsCapability->rollback($connectionName);
    }

    public function run(callable $callback, string|null $connectionName = null) : mixed
    {
        return $this->transactionsCapability->run($callback, $connectionName);
    }
}
