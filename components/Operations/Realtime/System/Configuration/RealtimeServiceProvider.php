<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Realtime\System\Configuration\RealtimeConfiguration;

/**
 * RealtimeServiceProvider — registers realtime component dependencies.
 */
final class RealtimeServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Realtime configuration — WebSocket and channel settings
        $container->singleton(RealtimeConfiguration::class, static fn () : RealtimeConfiguration => new RealtimeConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — Realtime uses static pools internally
    }
}
