<?php

declare(strict_types=1);

namespace Avax\Components\Presentation\View\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * ViewServiceProvider — registers view component dependencies.
 */
final class ViewServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Template engine and RenderView require runtime paths (view directory, compile directory).
        // These are assembled by RegisterViewDependencies at application boot time.
        // The ServiceProvider registers the configuration placeholder here.
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — View engine is assembled by RegisterViewDependencies
    }
}
