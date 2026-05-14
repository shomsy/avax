<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\RuntimeSupervision\System\Configuration\RuntimeSupervisionConfiguration;

/**
 * RuntimeSupervisionServiceProvider — registers runtime supervision component dependencies.
 */
final class RuntimeSupervisionServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Runtime supervision configuration
        $container->singleton(RuntimeSupervisionConfiguration::class, static fn () : RuntimeSupervisionConfiguration => new RuntimeSupervisionConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
