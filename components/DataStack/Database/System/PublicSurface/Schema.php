<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema as SchemaCapability;

/**
 * Public surface for Schema manipulation.
 */
final readonly class Schema
{
    public function __construct(
        private SchemaCapability $schemaCapability,
    ) {
    }

    public function create(string $table, callable $callback, string|null $connectionName = null) : void
    {
        $this->schemaCapability->create($table, $callback, $connectionName);
    }

    public function table(string $table, callable $callback, string|null $connectionName = null) : void
    {
        $this->schemaCapability->table($table, $callback, $connectionName);
    }

    public function drop(string $table, string|null $connectionName = null) : void
    {
        $this->schemaCapability->drop($table, $connectionName);
    }

    public function dropIfExists(string $table, string|null $connectionName = null) : void
    {
        $this->schemaCapability->dropIfExists($table, $connectionName);
    }

    public function truncate(string $table, string|null $connectionName = null) : void
    {
        $this->schemaCapability->truncate($table, $connectionName);
    }

    public function createDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->schemaCapability->createDatabase($name, $connectionName);
    }

    public function dropDatabase(string $name, string|null $connectionName = null) : void
    {
        $this->schemaCapability->dropDatabase($name, $connectionName);
    }
}
