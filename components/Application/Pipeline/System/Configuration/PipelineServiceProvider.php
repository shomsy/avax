<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Pipeline\System\PublicSurface\HookRegistry;

/**
 * PipelineServiceProvider — registers pipeline component dependencies.
 */
final class PipelineServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Hook registry — static hook registration for pipeline stages
        $container->singleton(HookRegistry::class, static fn () : HookRegistry => new HookRegistry());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — Pipeline uses static HookRegistry internally
    }
}
