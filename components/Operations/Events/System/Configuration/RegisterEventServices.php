<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Configuration;

use Avax\Components\Operations\Events\System\Capabilities\EventDispatcherInterface;

final class RegisterEventServices
{
    public static function register(EventDispatcherInterface $dispatcher): void
    {
        \Avax\Components\Operations\Events\System\PublicSurface\Events::setDispatcher($dispatcher);
    }
}