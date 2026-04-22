<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Database\System\Capabilities\Query\CreateBuilder\CreateBuilder;
use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;
use Avax\Database\System\Capabilities\Query\ValueObjects\Expression;
use Avax\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use ReflectionException;
use Throwable;

/**
 * Public capability owner for Laravel-like query DSL and SQL execution.
 */
final readonly class Query
{
    private CreateBuilder $createBuilder;

    public function __construct(
        private Connections         $connections,
        private EventBus|null       $eventBus = null,
        private GrammarInterface    $grammar = new Grammar\MySQLGrammar(),
        private ExecutionScope|null $scope = null,
        CreateBuilder|null          $createBuilder = null
    )
    {
        $this->createBuilder = $createBuilder ?? new CreateBuilder(
            connections: $this->connections,
            grammar    : $this->grammar,
            eventBus   : $this->eventBus,
            scope      : $this->scope
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

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function raw(string $value, string|null $connectionName = null) : Expression
    {
        return $this->builder(connectionName: $connectionName)->raw(value: $value);
    }

    public function grammar() : GrammarInterface
    {
        return $this->grammar;
    }
}
