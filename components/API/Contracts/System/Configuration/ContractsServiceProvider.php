<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

/**
 * ContractsServiceProvider — no-op provider for API/Contracts component.
 *
 * This component contains interfaces and contracts only.
 * No concrete implementations need to be registered.
 */
final class ContractsServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // This component contains interfaces only — no registrations needed.
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
