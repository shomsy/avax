<?php

/** @noinspection OverrideMissingInspection */

declare(strict_types=1);

namespace Avax\Facade\Facades;

use Avax\Facade\BaseFacade;

class Session extends BaseFacade
{
    /**
     * Get the registered name of the component.
     *
     * The getFacadeAccessor() method returns a string, 'Router',
     * which represents the registered name of the component in the service container.
     * This method is used by the service container to bind the Router instance to the application,
     * allowing for the facade to work seamlessly with the underlying implementation.
     *
     * @return string the registered name of the component in the service container
     */
    protected static string $accessor = 'Session';

}
