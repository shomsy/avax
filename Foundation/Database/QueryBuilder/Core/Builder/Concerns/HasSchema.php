<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Migrations\Design\Table\Blueprint;
use Throwable;

/**
 * Trait bridging the QueryBuilder with the Migration system for integrated schema management.
 *
 * -- intent:
 * Provides a high-level, fluent Domain Specific Language (DSL) for performing 
 * structural database modifications (creating tables, dropping schemas) 
 * directly within the builder context, facilitating rapid provisioning and teardown.
 *
 * -- invariants:
 * - Structural modifications must be dispatched via the QueryOrchestrator.
 * - Table creation must use the Blueprint abstraction to maintain dialect neutrality.
 * - Destructive operations (DROP/TRUNCATE) must be explicitly invoked with identifiers.
 *
 * -- boundaries:
 * - Does NOT handle the long-term versioning of schema changes (delegated to MigrationRepository).
 * - Does NOT perform safety checks beyond "IF EXISTS" clauses provided by grammar.
 */
trait HasSchema
{
    /**
     * Create a new database table structure fluently.
     *
     * -- intent:
     * Provide a standardized entry point for defining and executing table 
     * structural designs, delegating SQL generation to the Blueprint and Grammar.
     *
     * @param string   $table    The technical identifier for the new database table.
     * @param callable $callback A design closure receiving a Blueprint instance to define columns and indexes.
     * @throws Throwable If the SQL compilation for the dialect or physical execution fails.
     * @return void
     */
    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint(table: $table);
        $callback($blueprint);

        $statements = $blueprint->toSql(grammar: $this->grammar);

        foreach ($statements as $sql) {
            $this->orchestrator->execute(sql: $sql);
        }
    }

    /**
     * Remove a table from the database schema with built-in existence safety.
     *
     * -- intent:
     * Provide a safe, dialect-aware shorthand for destroying a table structure 
     * if it currently exists in the target database.
     *
     * @param string $table The technical name of the table to be removed.
     * @throws Throwable If the drop instruction execution fails at the driver level.
     * @return void
     */
    public function dropIfExists(string $table): void
    {
        $sql = $this->grammar->compileDropIfExists(table: $table);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * Efficiently clear all records from a database table without destroying its structure.
     *
     * -- intent:
     * Provide a low-level, high-performance reset mechanism (SQL TRUNCATE) for 
     * clearing raw data while preserving the schema and its indexes.
     *
     * @param string|null $table The optional technical name of the table (defaults to the builder's current source).
     * @throws Throwable If the truncate instruction execution fails.
     * @return void
     */
    public function truncate(string|null $table = null): void
    {
        $table = $table ?: $this->state->from;
        $sql   = $this->grammar->compileTruncate(table: $table);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * Create a new database container/schema.
     *
     * -- intent:
     * Provide a direct mechanism for provisioning high-level schema containers 
     * within the database cluster.
     *
     * @param string $name The technical identifier for the new database/schema.
     * @throws Throwable If the creation command fails at the persistence layer.
     * @return void
     */
    public function createDatabase(string $name): void
    {
        $sql = $this->grammar->compileCreateDatabase(name: $name);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * Permanently remove a database container/schema.
     *
     * -- intent:
     * Provide a direct mechanism for executing destructive schema-level removals.
     * WARNING: This operation is non-reversible and deletes all internal data.
     *
     * @param string $name The technical identifier of the database to be destroyed.
     * @throws Throwable If the destruction command fails.
     * @return void
     */
    public function dropDatabase(string $name): void
    {
        $sql = $this->grammar->compileDropDatabase(name: $name);
        $this->orchestrator->execute(sql: $sql);
    }
}
