<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\PublicSurface;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Migrations as MigrationsCapability;

/**
 * Public surface for Database Migrations.
 */
final readonly class Migrations
{
    public function __construct(
        private MigrationsCapability $migrationsCapability,
    ) {
    }

    public function status(string|null $connectionName = null) : array
    {
        return $this->migrationsCapability->status($connectionName);
    }

    public function migrate(string|null $connectionName = null) : void
    {
        $this->migrationsCapability->migrate($connectionName);
    }

    public function rollback(string|null $connectionName = null, int $steps = 1) : void
    {
        $this->migrationsCapability->rollback($connectionName, $steps);
    }

    public function seed(string $class, string|null $connectionName = null) : void
    {
        $this->migrationsCapability->seed($class, $connectionName);
    }
}
