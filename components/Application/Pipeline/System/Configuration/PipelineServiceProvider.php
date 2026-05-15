<?php

declare(strict_types=1);

namespace Avax\Components\Application\Pipeline\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Pipeline\System\Capabilities\PipelineHooks\HookRegistry;
use Avax\Components\Application\Pipeline\System\PublicSurface\Pipeline;

/**
 * PipelineServiceProvider — registers and boots the pipeline component.
 *
 * Registers HookRegistry as a singleton and wires it into the Pipeline facade during boot.
 */
final class PipelineServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        $container->singleton(HookRegistry::class, static fn () : HookRegistry => new HookRegistry());
    }

    public function boot(ContainerInterface $container) : void
    {
        Pipeline::setInstance($container->make(HookRegistry::class));
    }
}
