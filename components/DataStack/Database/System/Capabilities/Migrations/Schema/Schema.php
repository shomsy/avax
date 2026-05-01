<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Throwable;

/**
 * Public schema runtime layered over the migration design DSL.
 */
final readonly class Schema
{
    public function __construct(private Query $query) {}

    /**
     * @throws Throwable
     */
    public function create(string $table, callable $callback, string $connectionName = null) : void
    {
        $blueprint = new Blueprint(table: $table);
        $callback($blueprint);

        $this->runStatements(
            statements    : $blueprint->toSql(grammar: $this->grammar(connectionName: $connectionName)),
            connectionName: $connectionName,
        );
    }

    /**
     * @param list<string> $statements
     *
     * @throws Throwable
     */
    private function runStatements(array $statements, string $connectionName = null) : void
    {
        $builder = $this->builder(connectionName: $connectionName);

        foreach ($statements as $statement) {
            $builder->statement(query: $statement);
        }
    }

    /**
     * @throws Throwable
     */
    private function builder(string $connectionName = null) : QueryBuilder
    {
        return $this->query->builder(connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    private function grammar(string $connectionName = null) : GrammarInterface
    {
        return $this->builder(connectionName: $connectionName)->getGrammar();
    }

    /**
     * @throws Throwable
     */
    public function table(string $table, callable $callback, string $connectionName = null) : void
    {
        $blueprint = new Blueprint(table: $table)->setAlterMode();
        $callback($blueprint);

        $this->runStatements(
            statements    : $blueprint->toSql(grammar: $this->grammar(connectionName: $connectionName)),
            connectionName: $connectionName,
        );
    }

    /**
     * @throws Throwable
     */
    public function drop(string $table, string $connectionName = null) : void
    {
        $grammar = $this->grammar(connectionName: $connectionName);
        $this->builder(connectionName: $connectionName)->statement(
            query: 'DROP TABLE ' . $grammar->wrap(value: $table),
        );
    }

    /**
     * @throws Throwable
     */
    public function dropIfExists(string $table, string $connectionName = null) : void
    {
        $this->builder(connectionName: $connectionName)->statement(
            query: $this->grammar(connectionName: $connectionName)->compileDropIfExists(table: $table),
        );
    }

    /**
     * @throws Throwable
     */
    public function truncate(string $table, string $connectionName = null) : void
    {
        $this->builder(connectionName: $connectionName)->statement(
            query: $this->grammar(connectionName: $connectionName)->compileTruncate(table: $table),
        );
    }

    /**
     * @throws Throwable
     */
    public function createDatabase(string $name, string $connectionName = null) : void
    {
        $this->builder(connectionName: $connectionName)->statement(
            query: $this->grammar(connectionName: $connectionName)->compileCreateDatabase(name: $name),
        );
    }

    /**
     * @throws Throwable
     */
    public function dropDatabase(string $name, string $connectionName = null) : void
    {
        $this->builder(connectionName: $connectionName)->statement(
            query: $this->grammar(connectionName: $connectionName)->compileDropDatabase(name: $name),
        );
    }
}
