<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Migrations\Design\Table\Blueprint;
use Throwable;

/**
 * Trait HasSchema
 *
 * -- intent: bridge the query builder with the migration system for on-the-fly table management.
 */
trait HasSchema
{
    /**
     * Create a new database table and define its structure.
     *
     * -- intent: provide a fluent API for structural table creation within the builder context.
     *
     * @param string   $table    Technical table identifier
     * @param callable $callback Structural design closure
     *
     * @return void
     * @throws Throwable If SQL compilation or execution fails
     */
    public function create(string $table, callable $callback) : void
    {
        $blueprint = new Blueprint(table: $table);
        $callback($blueprint);

        $statements = $blueprint->toSql(grammar: $this->grammar);

        foreach ($statements as $sql) {
            $this->executor->execute(sql: $sql);
        }
    }

    /**
     * Drop a database table if it currently exists.
     *
     * -- intent: provide a safe shorthand for table destruction.
     *
     * @param string $table Technical table name
     *
     * @return void
     * @throws Throwable If SQL execution fails
     */
    public function dropIfExists(string $table) : void
    {
        $sql = $this->grammar->compileDropIfExists(table: $table);
        $this->executor->execute(sql: $sql);
    }

    /**
     * Remove all records from a table and reset auto-increment counters.
     *
     * -- intent: provide an efficient, low-level reset for a specific table.
     *
     * @param string|null $table Optional table name override
     *
     * @return void
     * @throws Throwable If SQL execution fails
     */
    public function truncate(string|null $table = null) : void
    {
        $table = $table ?: $this->state->from;
        $sql   = $this->grammar->compileTruncate(table: $table);
        $this->executor->execute(sql: $sql);
    }

    /**
     * Create a new database.
     *
     * -- intent: provide a direct mechanism for creating schema-level containers.
     *
     * @param string $name Database name
     *
     * @return void
     * @throws Throwable If SQL execution fails
     */
    public function createDatabase(string $name) : void
    {
        $sql = $this->grammar->compileCreateDatabase(name: $name);
        $this->executor->execute(sql: $sql);
    }

    /**
     * Drop a database.
     *
     * -- intent: provide a mechanism for destructive schema-level operations.
     *
     * @param string $name Database name
     *
     * @return void
     * @throws Throwable If SQL execution fails
     */
    public function dropDatabase(string $name) : void
    {
        $sql = $this->grammar->compileDropDatabase(name: $name);
        $this->executor->execute(sql: $sql);
    }
}
