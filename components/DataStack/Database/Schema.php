<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema as SchemaCapability;
use Throwable;

final readonly class Schema
{
    public function __construct(private SchemaCapability $schemaCapability) {}

    /**
     * @throws Throwable
     */
    public function create(string $table, callable $callback, ?string $connectionName = null) : void
    {
        $this->schemaCapability->create(table: $table, callback: $callback, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function table(string $table, callable $callback, ?string $connectionName = null) : void
    {
        $this->schemaCapability->table(table: $table, callback: $callback, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function drop(string $table, ?string $connectionName = null) : void
    {
        $this->schemaCapability->drop(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function dropIfExists(string $table, ?string $connectionName = null) : void
    {
        $this->schemaCapability->dropIfExists(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function truncate(string $table, ?string $connectionName = null) : void
    {
        $this->schemaCapability->truncate(table: $table, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function createDatabase(string $name, ?string $connectionName = null) : void
    {
        $this->schemaCapability->createDatabase(name: $name, connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function dropDatabase(string $name, ?string $connectionName = null) : void
    {
        $this->schemaCapability->dropDatabase(name: $name, connectionName: $connectionName);
    }
}
