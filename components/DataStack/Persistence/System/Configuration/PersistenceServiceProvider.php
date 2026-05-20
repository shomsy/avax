<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * PersistenceServiceProvider — DataStack/Persistence component service provider.
 *
 * This component is currently empty. No dependencies to register.
 */
final class PersistenceServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // No dependencies to register — component is empty
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed — component is empty
    }
}
