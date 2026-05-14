<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Security\DataProtection\System\Configuration\DataProtectionConfiguration;

/**
 * DataProtectionServiceProvider — registers data protection component dependencies.
 */
final class DataProtectionServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Data protection configuration — cipher and key settings
        $container->singleton(DataProtectionConfiguration::class, static fn () : DataProtectionConfiguration => new DataProtectionConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
