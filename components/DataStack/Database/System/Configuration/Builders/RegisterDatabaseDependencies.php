<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Configuration\Builders;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\RegisterDependency;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\CreateMigration\MigrationGenerator;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\ExportDatabase\DatabaseExporter;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\PublicSurface\Database;
use Avax\Components\DataStack\Database\System\PublicSurface\DatabaseInterface;
use Avax\Components\DataStack\Database\System\PublicSurface\Entities;
use Avax\Components\DataStack\Database\System\PublicSurface\Migrations;
use Avax\Components\DataStack\Database\System\PublicSurface\Query;
use Avax\Components\DataStack\Database\System\PublicSurface\Schema;
use Avax\Components\DataStack\Database\System\PublicSurface\Telemetry;
use Avax\Components\DataStack\Database\System\PublicSurface\Transactions;
use Random\RandomException;

/**
 * Optional Avax Container adapter for assembling the Database system.
 */
final readonly class RegisterDatabaseDependencies implements RegisterDependency
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function dependsOn(): array
    {
        return [];
    }

    public function register(): void
    {
        $this->container->singleton(abstract: DatabaseInterface::class, concrete: fn () : Database => $this->buildDatabase());
        $this->container->alias(abstract: DatabaseInterface::class, alias: Database::class);

        $this->container->singleton(abstract: Connections::class, concrete: function (): Connections {
            /** @var Database $database */
            $database = $this->container->get(id: Database::class);
            return $database->connections();
        });

        $this->container->singleton(abstract: Query::class, concrete: function (): Query {
            /** @var Database $database */
            $database = $this->container->get(id: Database::class);
            return $database->query();
        });

        $this->container->singleton(abstract: Migrations::class, concrete: function (): Migrations {
            /** @var Database $database */
            $database = $this->container->get(id: Database::class);
            return $database->migrations();
        });

        $this->container->singleton(abstract: Entities::class, concrete: function (): Entities {
            /** @var Database $database */
            $database = $this->container->get(id: Database::class);
            return $database->entities();
        });

        $this->container->singleton(abstract: Schema::class, concrete: function (): Schema {
            /** @var Database $database */
            $database = $this->container->get(id: Database::class);
            return $database->schema();
        });

        $this->container->singleton(abstract: Transactions::class, concrete: function (): Transactions {
            /** @var Database $database */
            $database = $this->container->get(id: Database::class);
            return $database->transactions();
        });

        $this->container->singleton(abstract: Telemetry::class, concrete: function (): Telemetry {
            /** @var Database $database */
            $database = $this->container->get(id: Database::class);
            return $database->telemetry();
        });

        $this->container->bind(abstract: QueryBuilder::class, concrete: function (): QueryBuilder {
            /** @var Query $query */
            $query = $this->container->get(id: Query::class);
            return $query->builder();
        });

        $this->container->singleton(abstract: MigrationLoader::class);
        $this->container->singleton(abstract: MigrationGenerator::class);
        $this->container->singleton(abstract: MigrationRepository::class);
        $this->container->singleton(abstract: MigrationRunner::class);
        $this->container->singleton(abstract: RollbackMigrations::class);
        $this->container->singleton(abstract: ReadMigrationStatus::class);
        $this->container->singleton(abstract: DatabaseExporter::class);
    }

    public function boot() : void {}

    /**
     * @throws RandomException
     */
    private function buildDatabase(): Database
    {
        return Database::configuration()
            ->usingConfig(config: $this->resolveDatabaseConfig())
            ->ready();
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveDatabaseConfig(): array
    {
        if (! $this->container->has(id: 'config')) {
            return [];
        }

        $config = $this->container->get(id: 'config');

        if (is_object(value: $config) && method_exists(object_or_class: $config, method: 'get')) {
            $resolved = $config->get('database', []);

            return is_array(value: $resolved) ? $resolved : [];
        }

        if (is_array(value: $config) && is_array(value: $config['database'] ?? null)) {
            return $config['database'];
        }

        return [];
    }
}
