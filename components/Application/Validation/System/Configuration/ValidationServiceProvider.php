<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Validation\System\PublicSurface\Validation;

/**
 * ValidationServiceProvider — registers validation component dependencies.
 */
final class ValidationServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Validation public surface — thin wrapper around ValidateData flow
        $container->singleton(Validation::class, static fn () : Validation => new Validation());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
