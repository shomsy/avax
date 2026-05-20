<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

final class SecurityServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        (new Builders\RegisterSecurityDefaults())->register($container);
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
