<?php

declare(strict_types=1);

namespace Avax\Components\Foundation\CallableSerialization\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;

final class CallableSerializationServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        (new Builders\RegisterCallableSerializationDefaults())->register($container);
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
