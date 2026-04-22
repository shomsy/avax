<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Querying\CreateBuilder;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Querying\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Querying\Execution\PDOExecutor;
use Avax\Database\System\Capabilities\Querying\Execution\QueryOrchestrator;
use Avax\Database\System\Capabilities\Querying\Grammar\GrammarInterface;
use Avax\Database\System\Capabilities\Querying\Identity\IdentityMap;
use Avax\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use ReflectionException;
use Throwable;

/**
 * Creates connection-bound QueryBuilder instances for the Querying capability.
 */
final readonly class CreateBuilder
{
    public function __construct(
        private Connections         $connections,
        private Transactions        $transactions,
        private GrammarInterface    $grammar,
        private EventBus|null       $eventBus = null,
        private ExecutionScope|null $scope = null
    ) {}

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function for(string|null $connectionName = null) : QueryBuilder
    {
        $connection   = $this->connections->connection(name: $connectionName);
        $transaction  = $this->transactions->on(connectionName: $connection->getName());
        $identityMap  = new IdentityMap(transactionManager: $transaction, connection: $connection);
        $orchestrator = new QueryOrchestrator(
            executor          : new PDOExecutor(
                                    connection    : $connection,
                                    eventBus      : $this->eventBus,
                                    connectionName: $connection->getName()
                                ),
            transactionManager: $transaction,
            identityMap       : $identityMap,
            scope             : $this->scope
        );

        return new QueryBuilder(
            grammar     : $this->grammar,
            orchestrator: $orchestrator
        );
    }
}
