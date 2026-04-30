<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Providers;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * Base class for service providers.
 */
abstract class BaseRegisterDependency
{
    public function __construct(
        protected readonly ContainerInterface $container,
    ) {}

    /**
     * Register services in the container.
     */
    abstract public function register() : void;

    /**
     * Boot services after all have been registered.
     */
    public function boot() : void
    {
        // Optional boot logic
    }
}
