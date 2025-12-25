<?php

declare(strict_types=1);

namespace Avax\Database\Registry;

use Avax\Container\Containers\DependencyInjector as Container;
use Avax\Database\Lifecycle\LifecycleInterface;
use Avax\Database\Registry\Exceptions\ModuleException;
use Throwable;

/**
 * Technical authority responsible for managing the registration and status of all modules.
 *
 * -- intent: maintain a central Map of active features and coordinate their lifecycle events.
 */
final class ModuleRegistry
{
    /**
     * Storage for instantiated and registered module objects.
     */
    private array $modules = [];

    /**
     * Tracks which modules have successfully passed the boot sequence.
     */
    private array $booted = [];

    /**
     * Register a new feature module into the internal and system containers.
     *
     * -- intent: instantiate the module and trigger its internal service definitions.
     *
     * @param string    $name      Logical identifier for the feature
     * @param string    $class     Fully qualified module class name
     * @param Container $container The target DI vessel
     *
     * @return void
     * @throws ModuleException If the module class is invalid or missing
     */
    public function register(string $name, string $class, Container $container) : void
    {
        if (! class_exists(class: $class)) {
            throw new ModuleException(moduleClass: $class, phase: 'registration', message: "Module class not found.");
        }

        try {
            $module = new $class(container: $container);

            if (! $module instanceof LifecycleInterface) {
                throw new ModuleException(
                    moduleClass: $class,
                    phase      : 'registration',
                    message    : "Module must implement LifecycleInterface."
                );
            }

            $module->register();
            $this->modules[$name] = $module;
        } catch (Throwable $e) {
            throw new ModuleException(
                moduleClass: $class,
                phase      : 'registration',
                message    : $e->getMessage(),
                previous   : $e
            );
        }
    }

    /**
     * Initiate the boot sequence for all registered and non-booted modules.
     *
     * -- intent: ensure every active feature is fully operational after registration.
     *
     * @return void
     * @throws ModuleException If any module fails to boot
     */
    public function boot() : void
    {
        foreach ($this->modules as $name => $module) {
            if (! isset($this->booted[$name])) {
                try {
                    $module->boot();
                    $this->booted[$name] = true;
                } catch (Throwable $e) {
                    throw new ModuleException(
                        moduleClass: $module::class,
                        phase      : 'boot',
                        message    : $e->getMessage(),
                        previous   : $e
                    );
                }
            }
        }
    }

    /**
     * Execute the graceful shutdown routine for all initialized modules.
     *
     * -- intent: trigger resource cleanup across all active features in the registry.
     *
     * @return void
     * @throws ModuleException If any module fails during shutdown
     */
    public function shutdown() : void
    {
        foreach ($this->modules as $module) {
            try {
                $module->shutdown();
            } catch (Throwable $e) {
                throw new ModuleException(
                    moduleClass: $module::class,
                    phase      : 'shutdown',
                    message    : $e->getMessage(),
                    previous   : $e
                );
            }
        }

        $this->modules = [];
        $this->booted  = [];
    }

    /**
     * Retrieve an active module instance by its technical name.
     *
     * -- intent: provide direct access to specific feature controllers.
     *
     * @param string $name Technical name of the feature
     *
     * @return LifecycleInterface
     * @throws ModuleException If the feature is not found in the registry
     */
    public function getModule(string $name) : LifecycleInterface
    {
        if (! isset($this->modules[$name])) {
            throw new ModuleException(moduleClass: $name, phase: 'retrieval', message: "Module not registered.");
        }

        return $this->modules[$name];
    }
}
