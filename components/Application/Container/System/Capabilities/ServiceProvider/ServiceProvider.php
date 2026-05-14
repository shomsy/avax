<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\ServiceProvider;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * ServiceProvider — composition root for a single component.
 *
 * Every component MUST have exactly one ServiceProvider.
 * The ServiceProvider registers all component dependencies in the container.
 *
 * Lifecycle:
 * 1. register() — declare all bindings (called first for all providers)
 * 2. boot()     — execute startup logic (called after all providers registered)
 */
interface ServiceProvider
{
    /**
     * Register all component dependencies in the container.
     *
     * This is the ONLY place where new Class() is allowed for DI registration.
     *
     * @param ContainerInterface $container The DI container
     */
    public function register(ContainerInterface $container) : void;

    /**
     * Execute startup logic after all dependencies are registered.
     *
     * Use this for: event subscriptions, middleware registration,
     * route compilation, cache warming, health checks.
     *
     * MUST be idempotent — calling boot() twice must not double-register.
     *
     * @param ContainerInterface $container The DI container
     */
    public function boot(ContainerInterface $container) : void;
}
