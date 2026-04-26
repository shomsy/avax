<?php

declare(strict_types=1);

use Avax\Auth\AuthInterface;

if (! function_exists(function: 'auth')) {
    /**
     * Retrieve the Authentication capability instance.
     *
     * @return AuthInterface
     */
    function auth() : AuthInterface
    {
        return app(abstract: AuthInterface::class);
    }
}
