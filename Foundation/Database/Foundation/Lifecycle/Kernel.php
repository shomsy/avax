<?php

declare(strict_types=1);

namespace Avax\Database\Lifecycle;

use Avax\Container\Read\DependencyInjector as Container;
use Avax\Database\Registry\ModuleRegistry;

/**
 * Central Bootstrapper (Kernel) for the database component lifecycle.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Architecture.md
 */
final readonly class Kernel
{
    /**
     * @param Container      $container The "Toolbox" where we store all our services.
     * @param ModuleRegistry $registry  The "Librarian" who keeps track of which modules are active.
     */
    public function __construct(
        private Container      $container,
        private ModuleRegistry $registry
    ) {}

    /**
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Architecture.md#kernel
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
     * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Architecture.md#kernel
     */
    public function shutdown() : void
    {
        $this->registry->shutdown();
    }
}
