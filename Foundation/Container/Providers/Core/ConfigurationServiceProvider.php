<?php

declare(strict_types=1);

namespace Avax\Container\Providers\Core;

use Avax\Config\Configurator\ConfiguratorInterface;
use Avax\Config\Configurator\FileLoader\ConfigFileLoader;
use Avax\Config\Configurator\FileLoader\ConfigLoaderInterface;
use Avax\Config\Service\Config;
use Avax\Container\Features\Operate\Boot\ServiceProvider;

/**
 * Service Provider for application configuration.
 *
 * @see docs_md/Providers/Core/ConfigurationServiceProvider.md#quick-summary
 */
class ConfigurationServiceProvider extends ServiceProvider
{
    /**
     * Register configuration bindings into the container.
     *
     * @return void
     * @see docs_md/Providers/Core/ConfigurationServiceProvider.md#method-register
     */
    public function register() : void
    {
        // Bind ConfigLoaderInterface to ConfigFileLoader
        $this->app->singleton(abstract: ConfigLoaderInterface::class, concrete: ConfigFileLoader::class);
        $this->app->singleton(abstract: ConfigFileLoader::class, concrete: ConfigFileLoader::class);

        // Bind ConfiguratorInterface to Config
        $this->app->singleton(abstract: ConfiguratorInterface::class, concrete: Config::class);
        $this->app->singleton(abstract: Config::class, concrete: Config::class);

        // Bind 'config' alias
        $this->app->singleton(abstract: 'config', concrete: function () {
            return $this->app->get(Config::class);
        });
    }
}
