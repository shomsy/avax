<?php

declare(strict_types=1);

namespace Avax\Database\Lifecycle;

use Avax\Container\Containers\DependencyInjector as Container;
use Avax\Database\Registry\ModuleRegistry;
use Throwable;

/**
 * System orchestrator responsible for managing the database component lifecycle.
 *
 * -- intent: coordinate the sequential registration and booting of all feature modules.
 */
final class Kernel
{
    /**
     * Constructor promoting core foundation dependencies via PHP 8.3 features.
     *
     * -- intent: link the container and registry for cross-component orchestration.
     *
     * @param Container      $container Central dependency injection vessel
     * @param ModuleRegistry $registry  Authority for module registration and status
     */
    public function __construct(
        private readonly Container      $container,
        private readonly ModuleRegistry $registry
    ) {}

    /**
     * Initiate the component boot sequence for all active modules.
     *
     * -- intent: transform all registered feature metadata into operational services.
     *
     * @return void
     * @throws Throwable If module registration or booting fails
     */
    public function boot() : void
    {
        $modules = Manifest::getModules();

        foreach ($modules as $name => $class) {
            $this->registry->register(name: $name, class: $class, container: $this->container);
        }

        $this->registry->boot();
    }

    /**
     * Execute the graceful shutdown routine for all initialized features.
     *
     * -- intent: ensure all active modules release their resources before system termination.
     *
     * @return void
     */
    public function shutdown() : void
    {
        $this->registry->shutdown();
    }
}
