<?php

declare(strict_types=1);

namespace Avax\Database\Integrations\AvaxContainer;

use Avax\Container\DI\Capabilities\Declaration\Providers\ServiceProviderInterface;
use Avax\Container\DI\ContainerInterface;
use Avax\Database\Database;
use Avax\Database\EntityManager;
use Avax\Database\Migrations;
use Avax\Database\Query;
use Avax\Database\Schema;
use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Migrations\CreateMigration\MigrationGenerator;
use Avax\Database\System\Capabilities\Migrations\ExportDatabase\DatabaseExporter;
use Avax\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use Avax\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Avax\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Database\Telemetry;
use Avax\Database\Transactions;

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
        $this->app->singleton(Database::class, function () {
            return $this->buildDatabase();
        });

        $this->app->singleton(Connections::class, function () {
            return $this->app->get(id: Database::class)->connections();
        });

        $this->app->singleton(Query::class, function () {
            return $this->app->get(id: Database::class)->query();
        });

        $this->app->singleton(Migrations::class, function () {
            return $this->app->get(id: Database::class)->migrations();
        });

        $this->app->singleton(EntityManager::class, function () {
            return $this->app->get(id: Database::class)->entityManager();
        });

        $this->app->singleton(Schema::class, function () {
            return $this->app->get(id: Database::class)->schema();
        });

        $this->app->singleton(Transactions::class, function () {
            return $this->app->get(id: Database::class)->transactions();
        });

        $this->app->singleton(Telemetry::class, function () {
            return $this->app->get(id: Database::class)->telemetry();
        });

        $this->app->bind(QueryBuilder::class, function () {
            return $this->app->get(id: Query::class)->builder();
        });

        $this->app->singleton(MigrationLoader::class);
        $this->app->singleton(MigrationGenerator::class);

        $this->app->singleton(MigrationRepository::class, function () {
            return $this->app->get(id: Migrations::class)->repository();
        });

        $this->app->singleton(MigrationRunner::class, function () {
            return $this->app->get(id: Migrations::class)->runner();
        });

        $this->app->singleton(RollbackMigrations::class, function () {
            return $this->app->get(id: Migrations::class)->rollbacker();
        });

        $this->app->singleton(ReadMigrationStatus::class, function () {
            return $this->app->get(id: Migrations::class)->status();
        });

        $this->app->singleton(DatabaseExporter::class, function () {
            return $this->app->get(id: Migrations::class)->exporter();
        });
    }

    public function boot() : void {}

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

    private function buildDatabase() : Database
    {
        return Database::configuration()
            ->usingConfig(config: $this->resolveDatabaseConfig())
            ->ready();
    }
}
