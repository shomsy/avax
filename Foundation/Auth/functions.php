<?php

declare(strict_types=1);

use Avax\Auth\System\Auth;

if (! function_exists(function: 'auth')) {
    /**
     * Resolve the authentication manager instance.
     *
     * @return Auth
     */
    function auth() : Auth
    {
        return app(abstract: Auth::class);
    }
}
