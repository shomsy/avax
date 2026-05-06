<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Configuration;

use Avax\Components\Operations\Events\System\Capabilities\Dispatcher\EventDispatcher;
use Avax\Components\Operations\Events\System\PublicSurface\Events;

final class RegisterEventDependencies
{
    public static function register(EventDispatcher $eventDispatcher): void
    {
        Events::setDispatcher($eventDispatcher);
    }
}
