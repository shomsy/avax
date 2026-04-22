<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\QueryBuilder;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\QueryBuilder\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\QueryBuilder\Execution\PDOExecutor;
use Avax\Database\System\Capabilities\QueryBuilder\Execution\QueryOrchestrator;
use Avax\Database\System\Capabilities\QueryBuilder\Grammar\GrammarInterface;
use Avax\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Avax\Database\System\Capabilities\Transactions\Identity\IdentityMap;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use ReflectionException;
use Throwable;

/**
 * Cohesive QueryBuilder/ORM capability.
 */
final readonly class QueryBuilderRuntime
{
    public function __construct(
        private Connections         $connections,
        private Transactions        $transactions,
        private EventBus|null       $eventBus = null,
        private GrammarInterface    $grammar = new Grammar\MySQLGrammar(),
        private ExecutionScope|null $scope = null
    ) {}

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function builder(string|null $connectionName = null) : QueryBuilder
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

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function from(string $table, string|null $connectionName = null) : QueryBuilder
    {
        return $this->builder(connectionName: $connectionName)->from(table: $table);
    }

    public function grammar() : GrammarInterface
    {
        return $this->grammar;
    }
}
