<?php

declare(strict_types=1);

namespace Avax\Database\Registry;

use Avax\Container\Read\DependencyInjector as Container;
use Avax\Database\Lifecycle\LifecycleInterface;
use Avax\Database\Registry\Exceptions\ModuleException;
use Throwable;

/**
 * Central registry for managing module lifecycles and registration.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Architecture.md
 */
final class ModuleRegistry
{
    /** @var array<string, LifecycleInterface> The collection of all features currently on the team. */
    private array $modules = [];

    /** @var array<string, bool> A list of which team members have finished their "Start-up" logic. */
    private array $booted = [];

    /**
     * Register a new module and its services into the container.
     *
     * @param string    $name      Module nickname.
     * @param string    $class     Module class name.
     * @param Container $container DI container.
     *
     * @throws ModuleException
     */
    public function register(string $name, string $class, Container $container) : void
    {
        if (! class_exists(class: $class)) {
            throw new ModuleException(moduleClass: $class, phase: 'registration', message: "Module class not found.");
        }

        try {
            // We create a fresh instance of the feature.
            $module = new $class(container: $container);

            if (! $module instanceof LifecycleInterface) {
                throw new ModuleException(
                    moduleClass: $class,
                    phase      : 'registration',
                    message    : "Module must implement LifecycleInterface."
                );
            }

            // We tell the feature to put its tools in the toolbox.
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
     * Transition all registered modules to the active (booted) state.
     *
     * @throws ModuleException
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
     * Shutdown all active modules and clear the registry.
     *
     * @throws ModuleException
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

        // Reset the registry to an empty state.
        $this->modules = [];
        $this->booted  = [];
    }

    /**
     * Find a specific active feature by its nickname.
     *
     * @param string $name The nickname (e.g., 'query_builder').
     *
     * @return LifecycleInterface The requested feature.
     */
    public function getModule(string $name) : LifecycleInterface
    {
        if (! isset($this->modules[$name])) {
            throw new ModuleException(moduleClass: $name, phase: 'retrieval', message: "Module not registered.");
        }

        return $this->modules[$name];
    }
}
