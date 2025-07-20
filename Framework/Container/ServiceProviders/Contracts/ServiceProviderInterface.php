<?php

declare(strict_types=1);

namespace Gemini\Container\ServiceProviders\Contracts;

/**
 * ServiceProviderInterface
 *
 * Defines the basic contract for service providers, including
 * methods for registering and booting services in the application container.
 */
interface ServiceProviderInterface
{
    /**
     * Registers services into the container.
     *
     * This is where bindings and singleton instances should be added
     * to make them available for dependency injection.
     */
    public function register() : void;

    /**
     * Boots registered services.
     *
     * Called after all services have been registered. Used to perform any
     * additional setup or configuration for services.
     */
    public function boot() : void;
}
