<?php

declare(strict_types=1);

namespace Avax\Database;

use Avax\Database\System\Capabilities\Migrations\CreateMigration\MigrationGenerator;
use Avax\Database\System\Capabilities\Migrations\ExportDatabase\DatabaseExporter;
use Avax\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Database\System\Capabilities\Migrations\Migrations as MigrationsCapability;
use Avax\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use Avax\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Avax\Database\System\Capabilities\Migrations\SeedDatabase\Seeder;
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
    public function runner(?string $connectionName = null) : MigrationRunner
    {
        return $this->migrations->runner(connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function rollbacker(?string $connectionName = null) : RollbackMigrations
    {
        return $this->migrations->rollbacker(connectionName: $connectionName);
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function status(?string $connectionName = null) : ReadMigrationStatus
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
    public function exporter(?string $connectionName = null) : DatabaseExporter
    {
        return $this->migrations->exporter(connectionName: $connectionName);
    }

    /**
     * @throws Throwable
     */
    public function seed(Seeder|string $seeder, ?string $connectionName = null) : void
    {
        $this->migrations->seed(seeder: $seeder, connectionName: $connectionName);
    }
}
