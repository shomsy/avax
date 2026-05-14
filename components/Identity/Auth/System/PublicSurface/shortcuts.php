<?php

declare(strict_types=1);

/**
 * Authentication shortcuts for global access.
 */

use Avax\Components\Identity\Auth\System\PublicSurface\Auth;

if (! function_exists('auth')) {
    /**
     * Resolve the authentication instance.
     */
    function auth() : Auth
    {
        return app(Auth::class);
    }
}
