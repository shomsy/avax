<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Transactions\RunTransaction;

use Avax\Components\DataStack\Database\System\Capabilities\Transactions\OnConnection\OnConnection;
use Throwable;

/**
 * Executes a callback inside a transaction resolved for one connection.
 */
final readonly class RunTransaction
{
    public function __construct(private OnConnection $onConnection)
    {
    }

    /**
     * @throws Throwable
     */
    public function run(callable $callback, ?string $connectionName = null): mixed
    {
        return $this->onConnection->for(connectionName: $connectionName)->transaction(callback: $callback);
    }
}
