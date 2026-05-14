<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Notifications\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Notifications\System\PublicSurface\Notifier;

/**
 * NotificationsServiceProvider — registers notifications component dependencies.
 */
final class NotificationsServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Notifier — public surface for sending notifications
        $container->singleton(Notifier::class, static fn () : Notifier => new Notifier());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — channels are registered at runtime
    }
}
