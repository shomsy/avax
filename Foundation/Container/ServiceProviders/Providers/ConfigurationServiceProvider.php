<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Config\Configurator\FileLoader\ConfigFileLoader;
use Avax\Container\ServiceProviders\ServiceProvider;
use Infrastructure\Config\Service\Config;

class ConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Registers configuration-related services in the IoC container.
     *
     * This approach ensures that the application configuration is
     * only instantiated once and shared across the entire application lifecycle.
     *
     */
    public function register() : void
    {
        // Register ConfigFileLoader as a singleton to make sure
        // it is used consistently across the application.
        $this->dependencyInjector->singleton(abstract: ConfigFileLoader::class, concrete: ConfigFileLoader::class);

        // Register Config as a singleton and use a closure to lazy-load it,
        // injecting the ConfigFileLoader instance as a dependency dynamically.
        $this->dependencyInjector->singleton(
            abstract: Config::class,
            concrete: fn() : Config => new Config(
                configLoader: $this->dependencyInjector->get(id: ConfigFileLoader::class)
            )
        );
    }

    /**
     * Starts the boot process for the class. This method can be optionally
     * overridden by derived classes to implement specific boot logic.
     * It outputs a default boot message.
     */
    public function boot() : void
    {
        // TODO: Implement boot() method.
    }
}