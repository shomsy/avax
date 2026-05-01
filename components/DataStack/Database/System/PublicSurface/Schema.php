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
        private SchemaCapability $schema,
    ) {}

    public function create(string $table, callable $callback, ?string $connectionName = null): void
    {
        $this->schema->create($table, $callback, $connectionName);
    }

    public function table(string $table, callable $callback, ?string $connectionName = null): void
    {
        $this->schema->table($table, $callback, $connectionName);
    }

    public function drop(string $table, ?string $connectionName = null): void
    {
        $this->schema->drop($table, $connectionName);
    }

    public function dropIfExists(string $table, ?string $connectionName = null): void
    {
        $this->schema->dropIfExists($table, $connectionName);
    }

    public function truncate(string $table, ?string $connectionName = null): void
    {
        $this->schema->truncate($table, $connectionName);
    }

    public function createDatabase(string $name, ?string $connectionName = null): void
    {
        $this->schema->createDatabase($name, $connectionName);
    }

    public function dropDatabase(string $name, ?string $connectionName = null): void
    {
        $this->schema->dropDatabase($name, $connectionName);
    }
}
