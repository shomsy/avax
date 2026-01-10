<?php

declare(strict_types=1);
namespace Avax\Container\Features\Operate\Boot;

/**
 * Base class for kernel service providers.
 *
 * WHAT IT DOES:
 * - Provides `register()` and `boot()` hooks for modules to bind and initialize services.
 *
 * WHY IT EXISTS:
 * - Centralizes container integration so modules can register their dependencies consistently.
 *
 * WHEN TO USE:
 * - Extend this class whenever a module needs to contribute bindings or perform boot-time initialization.
 *
 * HOW TO USE:
 * ```php
 * class MyProvider extends ServiceProvider
 * {
 *     public function register() : void { // bind definitions
 *     }
 *     public function boot() : void { // resolve services
 *     }
 * }
 * ```
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Keep `register()` idempotent and fast; deferred boot logic should resolve services.
 *
 * SECURITY CONSIDERATIONS:
 * - Delay resolving sensitive instances until `boot()` after the container is fully configured.
 *
 * @see docs/Features/Operate/Boot/ServiceProvider.md#quick-summary
 */
abstract class ServiceProvider
{
    public function __construct(
        protected Application $app
    ) {}

    /**
     * Register service bindings into the container.
     *
     * WARNING: Do not resolve services here (DB, etc.); only bind definitions.
     *
     * @see docs/Features/Operate/Boot/ServiceProvider.md#method-register
     */
    public function register() : void
    {
        // Optional
    }

    /**
     * Boot logic executed after all service providers have registered.
     *
     * Safe to resolve services and perform post-registration initialization here.
     *
     * @see docs/Features/Operate/Boot/ServiceProvider.md#method-boot
     */
    public function boot() : void
    {
        // Optional
    }
}
