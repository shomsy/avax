<?php

declare(strict_types=1);

namespace Avax\Migrations\Design;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Database\QueryBuilder\Core\Grammar\GrammarInterface;
use Avax\Migrations\Design\Table\Blueprint;
use Closure;
use RuntimeException;

/**
 * Base migration class providing fluent schema building methods (DSL).
 *
 * -- intent: provide the primary design tool for schema changes.
 */
abstract class BaseMigration
{
    /**
     * Query builder instance for executing migration statements.
     *
     * @var QueryBuilder|null
     */
    protected QueryBuilder|null $queryBuilder = null;

    /**
     * Set the query builder instance for this migration.
     *
     * -- intent: enable dependency injection instead of service locator pattern.
     *
     * @param QueryBuilder $builder Query builder instance
     *
     * @return void
     */
    public function setQueryBuilder(QueryBuilder $builder) : void
    {
        $this->queryBuilder = $builder;
    }

    /**
     * Run the migrations.
     */
    abstract public function up() : void;

    /**
     * Reverse the migrations.
     */
    abstract public function down() : void;

    /**
     * Create a new table in the database.
     *
     * @throws \Throwable
     */
    protected function create(string $table, Closure $callback) : void
    {
        $blueprint = new Blueprint(table: $table);
        $callback($blueprint);

        $grammar = $this->getGrammar();
        $sql     = $blueprint->toSql(grammar: $grammar);

        foreach ($sql as $statement) {
            $this->getConnection()->statement(query: $statement);
        }
    }

    /**
     * Get the grammar instance from the query builder.
     *
     * @return GrammarInterface
     * @throws RuntimeException If query builder is not set
     */
    private function getGrammar() : GrammarInterface
    {
        return $this->getConnection()->getGrammar();
    }

    /**
     * Get the query builder instance.
     *
     * -- intent: provide access to query builder with proper dependency injection.
     *
     * @return QueryBuilder
     * @throws RuntimeException If query builder is not set
     */
    protected function getConnection() : QueryBuilder
    {
        if ($this->queryBuilder === null) {
            throw new RuntimeException(
                message: 'QueryBuilder must be set on migration instance. ' .
                'Use setQueryBuilder() method or inject via constructor in migration classes.'
            );
        }

        return $this->queryBuilder;
    }

    /**
     * Modify an existing table.
     *
     * @throws \Throwable
     */
    protected function table(string $table, Closure $callback) : void
    {
        $blueprint = (new Blueprint(table: $table))->setAlterMode();
        $callback($blueprint);

        $grammar = $this->getGrammar();
        $sql     = $blueprint->toSql(grammar: $grammar);

        foreach ($sql as $statement) {
            $this->getConnection()->statement(query: $statement);
        }
    }

    /**
     * Drop a table if it exists.
     *
     * @throws \Throwable
     */
    protected function dropIfExists(string $table) : void
    {
        $this->drop(table: $table);
    }

    /**
     * Drop a table.
     *
     * @throws \Throwable
     */
    protected function drop(string $table) : void
    {
        $grammar = $this->getGrammar();
        $sql     = "DROP TABLE IF EXISTS " . $grammar->wrap(value: $table);

        $this->getConnection()->statement(query: $sql);
    }
}
