<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use Avax\Migrations\Design\Table\Blueprint;

/**
 * Trait bridging the QueryBuilder with the Migration system for integrated schema management.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Schema.md
 */
trait HasSchema
{
    /**
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Schema.md#create
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
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Schema.md#dropifexists
     */
    public function dropIfExists(string $table) : void
    {
        $sql = $this->grammar->compileDropIfExists(table: $table);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Schema.md#truncate
     */
    public function truncate(string|null $table = null) : void
    {
        $table = $table ?: $this->state->from;
        $sql   = $this->grammar->compileTruncate(table: $table);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Schema.md#createdatabase
     */
    public function createDatabase(string $name) : void
    {
        $sql = $this->grammar->compileCreateDatabase(name: $name);
        $this->orchestrator->execute(sql: $sql);
    }

    /**
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/Schema.md#dropdatabase
     */
    public function dropDatabase(string $name) : void
    {
        $sql = $this->grammar->compileDropDatabase(name: $name);
        $this->orchestrator->execute(sql: $sql);
    }
}
