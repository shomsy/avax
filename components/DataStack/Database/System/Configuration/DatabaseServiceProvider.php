<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\DataStack\Database\System\Capabilities\HealthCheck\CheckDatabaseHealth;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\QueryEvents\EventBus;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\GlobalDatabaseLifecycleState;

/**
 * DatabaseServiceProvider — registers database component dependencies.
 *
 * Note: Connection-level services (Connections, QueryOrchestrator, Transactions, etc.)
 * require runtime configuration (DSN, credentials, PDO instance) and are assembled
 * through DatabaseBuilder at application boot time, not here.
 * This ServiceProvider registers only the infrastructure that can be safely
 * configured without runtime connection details.
 */
final class DatabaseServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // EventBus — shared telemetry dispatcher (connection-independent)
        $container->singleton(EventBus::class, static fn () : EventBus => new EventBus());

        // Health check
        $container->singleton(CheckDatabaseHealth::class, static fn () : CheckDatabaseHealth => new CheckDatabaseHealth());
    }

    public function boot(ContainerInterface $container) : void
    {
        // Ensure lifecycle registry is initialized
        GlobalDatabaseLifecycleState::registry();

        // Ensure compiled registry is loadable
        class_exists(CompiledDatabaseLifecycleRegistry::class);
    }
}
