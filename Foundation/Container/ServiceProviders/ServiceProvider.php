<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders;

use Avax\Container\Containers\DependencyInjector;
use Avax\Container\ServiceProviders\Contracts\ServiceProviderInterface;

/**
 * Class ServiceProvider
 *
 * The base class for all service providers.
 */
abstract class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Constructor method for initializing the class with an application container.
     *
     * @param DependencyInjector $dependencyInjector The application container for managing dependencies.
     */
    public function __construct(protected DependencyInjector $dependencyInjector) {}

    /**
     * Registers the necessary services into the service container.
     */
    abstract public function register(): void;

    /**
     * Starts the boot process for the class.
     */
    abstract public function boot(): void;
}
