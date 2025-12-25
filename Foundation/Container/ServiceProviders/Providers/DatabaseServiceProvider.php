<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\Database\Connection\ConnectionManager;
use Avax\Database\Connection\Contracts\DatabaseConnection;
use Exception;
use Infrastructure\Config\Service\Config;
use Override;
use Throwable;

/**
 * Class DatabaseServiceProvider
 *
 * Registers and configures database-related services inside the dependency injection (DI) container.
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Registers database services in the DI container.
     */
    #[\Override]
    public function register(): void
    {
        // Bind ConnectionManager as Singleton
        $this->dependencyInjector->singleton(
            abstract: ConnectionManager::class,
            concrete: function () {
                $config = $this->loadDatabaseConfig();
                return new ConnectionManager(config: $config);
            }
        );

        // Bind the default DatabaseConnection
        $this->dependencyInjector->bind(
            abstract: DatabaseConnection::class,
            concrete: fn() => $this->dependencyInjector->get(id: ConnectionManager::class)->connection()
        );
    }

    /**
     * Performs an optional database connectivity test during boot.
     */
    #[\Override]
    public function boot(): void
    {
        try {
            $config = $this->loadDatabaseConfig();

            if (($config['healthcheck']['enabled'] ?? false) === true) {
                /** @var DatabaseConnection $connection */
                $connection = $this->dependencyInjector->get(id: DatabaseConnection::class);
                if (!$connection->ping()) {
                    throw new Exception(message: 'Database healthcheck failed.');
                }
            }
        } catch (Throwable $e) {
            // Logic to handle boot failure (e.g. logging)
        }
    }

    /**
     * Load database configuration from the system config service.
     *
     * @return array
     */
    private function loadDatabaseConfig(): array
    {
        try {
            if ($this->dependencyInjector->has(id: Config::class)) {
                /** @var Config $configService */
                $configService = $this->dependencyInjector->get(id: Config::class);
                return (array) $configService->get('database', []);
            }
        } catch (Throwable) {
            // Fallback if config service is not available
        }

        return [];
    }
}
