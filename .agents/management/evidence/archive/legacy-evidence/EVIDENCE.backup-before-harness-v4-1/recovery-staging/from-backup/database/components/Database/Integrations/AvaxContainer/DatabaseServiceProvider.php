<?php

declare(strict_types=1);

namespace components\Database\Integrations\AvaxContainer;

use components\Container\DI\Capabilities\Declaration\Providers\ServiceProviderInterface;
use components\Container\DI\ContainerInterface;
use components\Database\Database;
use components\Database\EntityManager;
use components\Database\Migrations;
use components\Database\Query;
use components\Database\Schema;
use components\Database\System\Capabilities\Connections\Connections;
use components\Database\System\Capabilities\Migrations\CreateMigration\MigrationGenerator;
use components\Database\System\Capabilities\Migrations\ExportDatabase\DatabaseExporter;
use components\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use components\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use components\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use components\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use components\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use components\Database\System\Capabilities\Query\Builder\QueryBuilder;
use components\Database\Telemetry;
use components\Database\Transactions;
use Random\RandomException;

/**
 * Optional Avax Container adapter for assembling the Database system.
 */
final readonly class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app) {}

    public function dependsOn() : array
    {
        return [];
    }

    public function register() : void
    {
        $this->app->singleton(abstract: Database::class, concrete: function () {
            return $this->buildDatabase();
        });

        $this->app->singleton(abstract: Connections::class, concrete: function () {
            return $this->app->get(id: Database::class)->connections();
        });

        $this->app->singleton(abstract: Query::class, concrete: function () {
            return $this->app->get(id: Database::class)->query();
        });

        $this->app->singleton(abstract: Migrations::class, concrete: function () {
            return $this->app->get(id: Database::class)->migrations();
        });

        $this->app->singleton(abstract: EntityManager::class, concrete: function () {
            return $this->app->get(id: Database::class)->entityManager();
        });

        $this->app->singleton(abstract: Schema::class, concrete: function () {
            return $this->app->get(id: Database::class)->schema();
        });

        $this->app->singleton(abstract: Transactions::class, concrete: function () {
            return $this->app->get(id: Database::class)->transactions();
        });

        $this->app->singleton(abstract: Telemetry::class, concrete: function () {
            return $this->app->get(id: Database::class)->telemetry();
        });

        $this->app->bind(abstract: QueryBuilder::class, concrete: function () {
            return $this->app->get(id: Query::class)->builder();
        });

        $this->app->singleton(abstract: MigrationLoader::class);
        $this->app->singleton(abstract: MigrationGenerator::class);

        $this->app->singleton(abstract: MigrationRepository::class, concrete: function () {
            return $this->app->get(id: Migrations::class)->repository();
        });

        $this->app->singleton(abstract: MigrationRunner::class, concrete: function () {
            return $this->app->get(id: Migrations::class)->runner();
        });

        $this->app->singleton(abstract: RollbackMigrations::class, concrete: function () {
            return $this->app->get(id: Migrations::class)->rollbacker();
        });

        $this->app->singleton(abstract: ReadMigrationStatus::class, concrete: function () {
            return $this->app->get(id: Migrations::class)->status();
        });

        $this->app->singleton(abstract: DatabaseExporter::class, concrete: function () {
            return $this->app->get(id: Migrations::class)->exporter();
        });
    }

    /**
     * @throws RandomException
     */
    private function buildDatabase() : Database
    {
        return Database::configuration()
            ->usingConfig(config: $this->resolveDatabaseConfig())
            ->ready();
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveDatabaseConfig() : array
    {
        if (! $this->app->has(id: 'config')) {
            return [];
        }

        $config = $this->app->get(id: 'config');

        if (is_object(value: $config) && method_exists(object_or_class: $config, method: 'get')) {
            $resolved = $config->get('database', []);

            return is_array(value: $resolved) ? $resolved : [];
        }

        if (is_array(value: $config) && is_array(value: $config['database'] ?? null)) {
            return $config['database'];
        }

        return [];
    }

    public function boot() : void {}
}
