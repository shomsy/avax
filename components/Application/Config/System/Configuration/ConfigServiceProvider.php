<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Configuration;

use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;
use Avax\Components\Application\Config\System\PublicSurface\Config;
use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * ConfigServiceProvider — registers config component dependencies.
 */
final class ConfigServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Configuration repository — mutable config store
        $container->singleton(ConfigurationRepository::class, static fn () : ConfigurationRepository => new ConfigurationRepository());

        // Config public surface — thin proxy to repository
        $container->singleton(Config::class, static fn (ContainerInterface $c) : Config => new Config(
            configurationRepository: $c->get(ConfigurationRepository::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
