<?php

declare(strict_types=1);

namespace Avax\Migrations\Design;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Migrations\Design\Table\Blueprint;
use Closure;

/**
 * Base migration class providing fluent schema building methods (DSL).
 *
 * -- intent: provide the primary design tool for schema changes.
 */
abstract class BaseMigration
{
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

    private function getGrammar()
    {
        return $this->getConnection()->getGrammar();
    }

    private function getConnection() : QueryBuilder
    {
        return app(QueryBuilder::class);
    }

    /**
     * Modify an existing table.
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
     */
    protected function dropIfExists(string $table) : void
    {
        $this->drop(table: $table);
    }

    /**
     * Drop a table.
     */
    protected function drop(string $table) : void
    {
        $grammar = $this->getGrammar();
        $sql     = "DROP TABLE IF EXISTS " . $grammar->wrap(value: $table);

        $this->getConnection()->statement(query: $sql);
    }
}
