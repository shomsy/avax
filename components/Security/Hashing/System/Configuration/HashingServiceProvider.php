<?php

declare(strict_types=1);

namespace Avax\Components\Security\Hashing\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Security\Hashing\System\Configuration\HashingConfiguration;

/**
 * HashingServiceProvider — registers hashing component dependencies.
 */
final class HashingServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Hashing configuration — default hashing algorithm settings
        $container->singleton(HashingConfiguration::class, static fn () : HashingConfiguration => new HashingConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — Hasher uses static helpers
    }
}
