<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Database;

use Avax\Container\Features\Operate\Boot\ServiceProvider;
use Avax\Database\Connection\ConnectionManager;
use Avax\Database\Events\EventBus;

/**
 * Service Provider for database services.
 *
 * @see docs/Providers/Database/DatabaseServiceProvider.md#quick-summary
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register database event bus, connection manager, and alias.
     *
     * @return void
     * @see docs/Providers/Database/DatabaseServiceProvider.md#method-register
     */
    public function register() : void
    {
        $this->app->singleton(abstract: EventBus::class, concrete: EventBus::class);

        $this->app->singleton(abstract: ConnectionManager::class, concrete: function () {
            // Retrieve database configuration
            $config = $this->app->get('config')->get('database', []);

            return new ConnectionManager(
                config  : $config,
                eventBus: $this->app->has(EventBus::class) ? $this->app->get(EventBus::class) : null
            );
        });

        // Bind 'db' alias
        $this->app->singleton(abstract: 'db', concrete: function () {
            return $this->app->get(ConnectionManager::class);
        });
    }
}
