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
        private MigrationsCapability $migrations,
    ) {}

    public function status(?string $connectionName = null): array
    {
        return $this->migrations->status($connectionName);
    }

    public function migrate(?string $connectionName = null): void
    {
        $this->migrations->migrate($connectionName);
    }

    public function rollback(?string $connectionName = null, int $steps = 1): void
    {
        $this->migrations->rollback($connectionName, $steps);
    }

    public function seed(string $class, ?string $connectionName = null): void
    {
        $this->migrations->seed($class, $connectionName);
    }
}
