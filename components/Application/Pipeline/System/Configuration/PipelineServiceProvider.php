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
    /**
     * Register pipeline hook services in the container.
     *
     * Registers HookRegistry as a singleton. The registry starts empty
     * and is populated through the Pipeline facade public API.
     */
    public function register(ContainerInterface $container) : void
    {
        $container->singleton(HookRegistry::class, static fn () : HookRegistry => new HookRegistry());
    }

    /**
     * Boot the pipeline component by wiring the facade.
     *
     * Resolves the registered HookRegistry singleton and injects it
     * into the Pipeline static facade via setInstance().
     * This is the single source of truth for pipeline hook execution.
     */
    public function boot(ContainerInterface $container) : void
    {
        Pipeline::setInstance($container->make(HookRegistry::class));
    }
}
