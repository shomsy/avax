<?php

declare(strict_types=1);

namespace Avax\Components\Application\Facade\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Facade\System\Capabilities\Registry\FacadeRegistry;

/**
 * FacadeServiceProvider — registers facade component dependencies.
 */
final class FacadeServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Facade registry — static registry for facade instances
        $container->singleton(FacadeRegistry::class, static fn () : FacadeRegistry => new FacadeRegistry());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — FacadeRegistry uses static state internally
    }
}
