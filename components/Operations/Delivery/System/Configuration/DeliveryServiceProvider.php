<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Delivery\System\Configuration\DeliveryConfiguration;

/**
 * DeliveryServiceProvider — registers delivery component dependencies.
 */
final class DeliveryServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Delivery configuration — build and release settings
        $container->singleton(DeliveryConfiguration::class, static fn () : DeliveryConfiguration => new DeliveryConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
