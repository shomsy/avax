<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Security\Privacy\System\Configuration\PrivacyConfiguration;

/**
 * PrivacyServiceProvider — registers privacy component dependencies.
 */
final class PrivacyServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Privacy configuration — retention and export settings
        $container->singleton(PrivacyConfiguration::class, static fn () : PrivacyConfiguration => new PrivacyConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
