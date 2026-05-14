<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Providers;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * Base class for dependency registration providers.
 */
abstract class BaseRegisterDependency
{
    public function __construct(
        protected readonly ContainerInterface $container,
    ) {
    }

    /**
     * Register dependencies in the container.
     */
    abstract public function register(): void;

    /**
     * Boot dependencies after registration.
     */
    public function boot(): void
    {
    }
}
