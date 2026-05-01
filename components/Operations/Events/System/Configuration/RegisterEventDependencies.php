<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Configuration;

use Avax\Components\Operations\Events\System\Capabilities\EventDispatcherInterface;
use Avax\Components\Operations\Events\System\PublicSurface\Events;

final class RegisterEventDependencies
{
    public static function register(EventDispatcherInterface $eventDispatcher) : void
    {
        Events::setDispatcher($eventDispatcher);
    }
}
