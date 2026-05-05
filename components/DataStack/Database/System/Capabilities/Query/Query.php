<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\CreateBuilder\CreateBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects\Expression;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use ReflectionException;
use Throwable;

/**
 * Public capability owner for Laravel-like query DSL and SQL execution.
 */
final readonly class Query
{
    private CreateBuilder $createBuilder;

    public function __construct(
        private Connections $connections,
        private ?EventBus $eventBus = null,
        private GrammarInterface $grammar = new MySQLGrammar(),
        private ?ExecutionScope  $executionScope = null,
        ?CreateBuilder           $createBuilder = null,
    ) {
        $this->createBuilder = $createBuilder ?? new CreateBuilder(
            connections: $this->connections,
            grammar    : $this->grammar,
            eventBus   : $this->eventBus,
            executionScope: $this->executionScope,
        );
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function from(string $table, ?string $connectionName = null) : QueryBuilder
    {
        return $this->builder(connectionName: $connectionName)->from(table: $table);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function builder(?string $connectionName = null) : QueryBuilder
    {
        return $this->createBuilder->for(connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function raw(string $value, ?string $connectionName = null) : Expression
    {
        return $this->builder(connectionName: $connectionName)->raw(value: $value);
    }

    public function grammar(): GrammarInterface
    {
        return $this->grammar;
    }
}
