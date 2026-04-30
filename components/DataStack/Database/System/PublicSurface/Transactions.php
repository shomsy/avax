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
        private TransactionsCapability $transactions,
    ) {}

    public function begin(string|null $connectionName = null) : void
    {
        $this->transactions->begin($connectionName);
    }

    public function commit(string|null $connectionName = null) : void
    {
        $this->transactions->commit($connectionName);
    }

    public function rollback(string|null $connectionName = null) : void
    {
        $this->transactions->rollback($connectionName);
    }

    public function run(callable $callback, string|null $connectionName = null) : mixed
    {
        return $this->transactions->run($callback, $connectionName);
    }
}
