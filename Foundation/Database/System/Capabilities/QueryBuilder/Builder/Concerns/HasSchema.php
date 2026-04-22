<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\QueryBuilder\Builder\Concerns;

use Avax\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use Throwable;

/**
 * Trait bridging the QueryBuilder with the Migration system for integrated schema management.
 *
 * @see /docs/Foundation/Database/DSL/Schema.md
 */
trait HasSchema
{
    /**
     * @see /docs/Foundation/Database/DSL/Schema.md#create
     *
     * @throws Throwable
     */
    public function create(string $table, callable $callback) : void
    {
        $blueprint = new Blueprint(table: $table);
        $callback($blueprint);

        $statements = $blueprint->toSql(grammar: $this->grammar);

        foreach ($statements as $sql) {
            $this->orchestrator->execute(sql: $sql);
        }
    }

    /**
     * @see /docs/Foundation/Database/DSL/Schema.md#dropifexists
     *
     * @throws Throwable
     */
    public function dropIfExists(string $table) : void
    {
        $sql = $this->grammar->compileDropIfExists(table: $table);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * @see /docs/Foundation/Database/DSL/Schema.md#truncate
     *
     * @throws Throwable
     */
    public function truncate(string|null $table = null) : void
    {
        $table = $table ?: $this->state->from;
        $sql   = $this->grammar->compileTruncate(table: $table);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * @see /docs/Foundation/Database/DSL/Schema.md#createdatabase
     *
     * @throws Throwable
     */
    public function createDatabase(string $name) : void
    {
        $sql = $this->grammar->compileCreateDatabase(name: $name);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * @see /docs/Foundation/Database/DSL/Schema.md#dropdatabase
     *
     * @throws Throwable
     */
    public function dropDatabase(string $name) : void
    {
        $sql = $this->grammar->compileDropDatabase(name: $name);
        $this->orchestrator->execute(sql: $sql);
    }
}
