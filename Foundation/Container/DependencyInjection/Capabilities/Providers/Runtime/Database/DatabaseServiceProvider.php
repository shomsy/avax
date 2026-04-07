<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Providers\Runtime\Database;

use Avax\Container\DependencyInjection\Capabilities\Providers\Runtime\ServiceProvider;
use Avax\Database\Connection\ConnectionManager;
use Avax\Database\Events\EventBus;

/**
 * Service Provider for database services.
 *
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register database event bus, connection manager, and alias.
     *
     */
    public function register() : void
    {
        $this->app->singleton(abstract: EventBus::class, concrete: EventBus::class);

        $this->app->singleton(abstract: ConnectionManager::class, concrete: function () {
            // Retrieve database configuration
            $config = $this->app->get(id: 'config')->get(key: 'database', default: []);

            return new ConnectionManager(
                config  : $config,
                eventBus: $this->app->has(id: EventBus::class) ? $this->app->get(id: EventBus::class) : null
            );
        });

        // Bind 'db' alias
        $this->app->singleton(abstract: 'db', concrete: function () {
            return $this->app->get(id: ConnectionManager::class);
        });
    }
}
