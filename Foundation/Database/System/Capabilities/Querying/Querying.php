<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Querying;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Querying\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Querying\CreateBuilder\CreateBuilder;
use Avax\Database\System\Capabilities\Querying\Grammar\GrammarInterface;
use Avax\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use ReflectionException;
use Throwable;

/**
 * Cohesive QueryBuilder/ORM capability.
 */
final readonly class Querying
{
    private CreateBuilder $createBuilder;

    public function __construct(
        private Connections         $connections,
        private Transactions        $transactions,
        private EventBus|null       $eventBus = null,
        private GrammarInterface    $grammar = new Grammar\MySQLGrammar(),
        private ExecutionScope|null $scope = null,
        CreateBuilder|null          $createBuilder = null
    )
    {
        $this->createBuilder = $createBuilder ?? new CreateBuilder(
            connections : $this->connections,
            transactions: $this->transactions,
            grammar     : $this->grammar,
            eventBus    : $this->eventBus,
            scope       : $this->scope
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

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function builder(string|null $connectionName = null) : QueryBuilder
    {
        return $this->createBuilder->for(connectionName: $connectionName);
    }

    public function grammar() : GrammarInterface
    {
        return $this->grammar;
    }
}
