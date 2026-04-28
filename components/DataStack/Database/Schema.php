<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema as SchemaCapability;
use Throwable;

final readonly class Schema
{
    public function __construct(private SchemaCapability $schema) {}

    /**
     * @throws Throwable
     */
    public function create(string $table, callable $callback, string|null $connectionName = null) : void
    {
        $this->schema->create(table: $table, callback: $callback, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function table(string $table, callable $callback, string|null $connectionName = null) : void
    {
        $this->schema->table(table: $table, callback: $callback, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function drop(string $table, string|null $connectionName = null) : void
    {
        $this->schema->drop(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function dropIfExists(string $table, string|null $connectionName = null) : void
    {
        $this->schema->dropIfExists(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function truncate(string $table, string|null $connectionName = null) : void
    {
        $this->schema->truncate(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function createDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->schema->createDatabase(name: $name, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function dropDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->schema->dropDatabase(name: $name, connectionName: $connectionName);
    }
}
