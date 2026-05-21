<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Credentials\System\Configuration\CredentialsConfiguration;

/**
 * CredentialsServiceProvider — registers credentials component dependencies.
 */
final class CredentialsServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Credentials configuration — encryption and credential limits
        $container->singleton(CredentialsConfiguration::class, static fn () : CredentialsConfiguration => new CredentialsConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed for the current configuration defaults.
    }
}
