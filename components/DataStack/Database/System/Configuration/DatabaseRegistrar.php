<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Configuration;

use Avax\Components\Application\Container\DI\Capabilities\Declaration\Providers\RegisterDependency;
use Avax\Components\Application\Container\DI\ContainerInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\LoadMigrations\MigrationLoader;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\ReadMigrationStatus\ReadMigrationStatus;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RollbackMigrations\RollbackMigrations;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\PublicSurface\Database;
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
    public function __construct(private ContainerInterface $container) {}

    public function dependsOn(): array
    {
        return [];
    }

    public function register(): void
    {
        $this->container->singleton(abstract: Database::class, concrete: fn (): Database => $this->buildDatabase());

        $this->container->singleton(abstract: Connections::class, concrete: fn () => $this->container->get(id: Database::class)->connections());

        $this->container->singleton(abstract: Query::class, concrete: fn () => $this->container->get(id: Database::class)->query());

        $this->container->singleton(abstract: Migrations::class, concrete: fn () => $this->container->get(id: Database::class)->migrations());

        $this->container->singleton(abstract: Entities::class, concrete: fn () => $this->container->get(id: Database::class)->entities());

        $this->container->singleton(abstract: Schema::class, concrete: fn () => $this->container->get(id: Database::class)->schema());

        $this->container->singleton(abstract: Transactions::class, concrete: fn () => $this->container->get(id: Database::class)->transactions());

        $this->container->singleton(abstract: Telemetry::class, concrete: fn () => $this->container->get(id: Database::class)->telemetry());

        $this->container->bind(abstract: QueryBuilder::class, concrete: fn () => $this->container->get(id: Query::class)->builder());

        $this->container->singleton(abstract: MigrationLoader::class);

        $this->container->singleton(abstract: MigrationRepository::class, concrete: fn () => $this->container->get(id: Migrations::class)->repository());

        $this->container->singleton(abstract: MigrationRunner::class, concrete: fn () => $this->container->get(id: Migrations::class)->runner());

        $this->container->singleton(abstract: RollbackMigrations::class, concrete: fn () => $this->container->get(id: Migrations::class)->rollbacker());

        $this->container->singleton(abstract: ReadMigrationStatus::class, concrete: fn () => $this->container->get(id: Migrations::class)->status());
    }

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
