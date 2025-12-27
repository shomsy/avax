<?php

declare(strict_types=1);

namespace Avax\Database\Lifecycle;

use Avax\Container\Containers\DependencyInjector as Container;
use Avax\Database\Registry\ModuleRegistry;
use Throwable;

/**
 * Central Bootstrapper (Kernel) for the database component lifecycle.
 *
 * @see docs/Concepts/Architecture.md
 */
final class Kernel
{
    /**
     * @param Container      $container The "Toolbox" where we store all our services.
     * @param ModuleRegistry $registry  The "Librarian" who keeps track of which modules are active.
     */
    public function __construct(
        private readonly Container      $container,
        private readonly ModuleRegistry $registry
    ) {}

    /**
     * Wake up the entire database system.
     *
     * @return void
     */
    public function boot(): void
    {
        $modules = Manifest::getModules();

        foreach ($modules as $name => $class) {
            $this->registry->register(name: $name, class: $class, container: $this->container);
        }

        $this->registry->boot();
    }

    /**
     * Shut everything down safely and close resources.
     *
     * @return void
     */
    public function shutdown(): void
    {
        $this->registry->shutdown();
    }
}
