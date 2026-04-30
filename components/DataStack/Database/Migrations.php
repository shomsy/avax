<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\CreateMigration\MigrationGenerator;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\ExportDatabase\DatabaseExporter;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Migrations as MigrationsCapability;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\SeedDatabase\Seeder;
use ReflectionException;
use Throwable;

final readonly class Migrations
{
    public function __construct(private MigrationsCapability $migrations) {}

    public function loader() : MigrationLoader
    {
        return $this->migrations->loader();
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function runner(string|null $connectionName = null) : MigrationRunner
    {
        return $this->migrations->runner(connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function rollbacker(string|null $connectionName = null) : RollbackMigrations
    {
        return $this->migrations->rollbacker(connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function status(string|null $connectionName = null) : ReadMigrationStatus
    {
        return $this->migrations->status(connectionName: $connectionName);
    }

    public function generator() : MigrationGenerator
    {
        return $this->migrations->generator();
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function exporter(string|null $connectionName = null) : DatabaseExporter
    {
        return $this->migrations->exporter(connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function seed(Seeder|string $seeder, string|null $connectionName = null) : void
    {
        $this->migrations->seed(seeder: $seeder, connectionName: $connectionName);
    }
}
