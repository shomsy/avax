<?php

declare(strict_types=1);

/**
 * Authentication shortcuts for global access.
 *
 * @deprecated Inject AuthInterface through constructor or method autowiring instead.
 *             This helper uses a service locator pattern which is forbidden in runtime code.
 *             The global instance is set during ServiceProvider boot via Auth::setInstance().
 */

use Avax\Components\Identity\Auth\System\PublicSurface\Auth;

if (! function_exists('auth')) {
    /**
     * Resolve the authentication instance.
     *
     * @deprecated Use DI injection of AuthInterface instead.
     */
    function auth() : Auth
    {
        return Auth::instance();
    }
}
